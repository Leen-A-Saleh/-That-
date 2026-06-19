<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/notifications-database.php';
require_once __DIR__ . '/stripe-client.php';

$config = require __DIR__ . '/../../Database/config.php';
$webhookSecret = $config['stripe_webhook_secret'];

// Minimal local logger
function webhook_log(string $message): void {
    $logFile = __DIR__ . '/webhook.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

webhook_log("Received webhook payload (length: " . strlen($payload) . ")");

$stripeClient = new StripeClient($config['stripe_secret_key']);

// If a webhook secret is provided, verify signature
if ($webhookSecret !== '') {
    if (!$stripeClient->verifyWebhookSignature($payload, $sigHeader, $webhookSecret)) {
        webhook_log("Error: Invalid webhook signature");
        http_response_code(400);
        die('Invalid signature');
    }
    webhook_log("Webhook signature verified");
} else {
    webhook_log("Warning: No webhook secret configured, skipping signature verification");
}

$event = json_decode($payload, true);

if (!$event || !isset($event['type'])) {
    webhook_log("Error: Invalid JSON payload");
    http_response_code(400);
    die('Invalid payload');
}

webhook_log("Event type: " . $event['type']);

if ($event['type'] === 'checkout.session.completed') {
    $session = $event['data']['object'];

    if (($session['payment_status'] ?? '') === 'paid') {
        webhook_log("Session payment_status is 'paid'");
        
        $appointmentId = (int) ($session['metadata']['appointment_id'] ?? 0);
        $clientId = (int) ($session['metadata']['client_id'] ?? 0);
        $therapistId = (int) ($session['metadata']['therapist_id'] ?? 0);
        $amountPaidCents = (int) ($session['amount_total'] ?? 0);
        $amountPaid = $amountPaidCents / 100;
        
        $paymentIntentId = $session['payment_intent'] ?? '';
        $transactionId = $session['id'] ?? '';

        webhook_log("Processing appointment ID: $appointmentId, Client: $clientId, Therapist: $therapistId, Amount: $amountPaid");

        if ($appointmentId > 0) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    UPDATE appointments 
                    SET status = 'CONFIRMED', 
                        payment_status = 'PAID',
                        stripe_payment_intent_id = ?,
                        stripe_transaction_id = ?,
                        paid_amount = ?,
                        payment_completed_at = NOW()
                    WHERE appointment_id = ? AND status = 'AWAITING_PAYMENT'
                ");
                $stmt->execute([$paymentIntentId, $transactionId, $amountPaid, $appointmentId]);

                if ($stmt->rowCount() > 0) {
                    $pdo->commit();
                    webhook_log("Database updated successfully for appointment ID: $appointmentId");

                    // Send Notifications
                    create_user_notification(
                        $clientId,
                        'نجاح عملية الدفع',
                        'تم تأكيد موعدك بنجاح بعد إتمام عملية الدفع.',
                        'SESSION_CONFIRMATION',
                        'NORMAL'
                    );

                    create_user_notification(
                        $therapistId,
                        'تأكيد الموعد بالدفع',
                        'أتم العميل الدفع وتم تأكيد الموعد.',
                        'SESSION_CONFIRMATION',
                        'NORMAL'
                    );
                    webhook_log("Notifications sent to Client $clientId and Therapist $therapistId");
                } else {
                    $pdo->rollBack();
                    webhook_log("Warning: Appointment $appointmentId not updated. Either not found or not in AWAITING_PAYMENT status.");
                }
            } catch (Throwable $e) {
                $pdo->rollBack();
                webhook_log("DB Error: " . $e->getMessage());
                http_response_code(500);
                die('DB Error');
            }
        } else {
            webhook_log("Error: appointment_id metadata is missing or 0.");
        }
    } else {
        webhook_log("Session payment_status is not 'paid' (status: " . ($session['payment_status'] ?? 'null') . ")");
    }
}

http_response_code(200);
echo 'Webhook processed.';
