<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/stripe-client.php';

start_secure_session();

if (!is_authenticated() || ($_SESSION['auth']['role'] ?? '') !== 'CLIENT') {
    die('Unauthorized');
}

$clientId = (int) $_SESSION['auth']['user_id'];
$appointmentId = (int) ($_GET['appointment_id'] ?? 0);

if ($appointmentId <= 0) {
    die('Invalid appointment ID');
}

$pdo = db();

// Fetch appointment
$stmt = $pdo->prepare("
    SELECT a.*, p.amount 
    FROM appointments a
    LEFT JOIN payments p ON (a.client_id = p.client_id AND a.therapist_id = p.therapist_id)
    WHERE a.appointment_id = ? AND a.client_id = ? AND a.status = 'AWAITING_PAYMENT'
    ORDER BY p.created_at DESC
    LIMIT 1
");
$stmt->execute([$appointmentId, $clientId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die('Appointment not found or not awaiting payment.');
}

// Ensure amount exists
$amount = (float) ($appointment['amount'] ?? 150.00); // fallback amount
$amountCents = (int) ($amount * 100);

$config = require __DIR__ . '/../../Database/config.php';
$stripeClient = new StripeClient($config['stripe_secret_key']);

$successUrl = $config['app_url'] . '/Public/handlers/stripe-success.php?session_id={CHECKOUT_SESSION_ID}';
$cancelUrl = $config['app_url'] . '/Client/client-appointments-page/appointments.php';

try {
    $session = $stripeClient->createCheckoutSession([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'sar',
                    'product_data' => [
                        'name' => 'جلسة استشارة نفسية',
                    ],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]
        ],
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'metadata' => [
            'appointment_id' => $appointmentId,
            'client_id' => $clientId,
            'therapist_id' => $appointment['therapist_id'],
        ],
    ]);

    if (!isset($session['url'])) {
        throw new Exception('Failed to create checkout session URL.');
    }

    // Optionally save the checkout session ID in appointments
    $updateStmt = $pdo->prepare("UPDATE appointments SET stripe_checkout_session_id = ? WHERE appointment_id = ?");
    $updateStmt->execute([$session['id'], $appointmentId]);

    header('Location: ' . $session['url']);
    exit;

} catch (Throwable $e) {
    die('Stripe Checkout Error: ' . $e->getMessage());
}
