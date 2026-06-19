<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/notifications-database.php';

start_secure_session();

if (!is_authenticated() || ($_SESSION['auth']['role'] ?? '') !== 'CLIENT') {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid method');
}

$clientId = (int) $_SESSION['auth']['user_id'];
$appointmentId = (int) ($_POST['appointment_id'] ?? 0);
$amount = (float) ($_POST['amount'] ?? 0);

if ($appointmentId <= 0) {
    die('Invalid appointment ID');
}

$pdo = db();

// Check if it's still awaiting payment
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND client_id = ? AND status = 'AWAITING_PAYMENT' LIMIT 1");
$stmt->execute([$appointmentId, $clientId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die('Appointment not found or not awaiting payment.');
}

$therapistId = (int) $appointment['therapist_id'];
$transactionId = 'mock_txn_' . bin2hex(random_bytes(8));

try {
    $pdo->beginTransaction();

    $updateStmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'CONFIRMED', 
            payment_status = 'PAID',
            stripe_transaction_id = ?,
            paid_amount = ?,
            payment_completed_at = NOW()
        WHERE appointment_id = ? AND status = 'AWAITING_PAYMENT'
    ");
    
    $updateStmt->execute([$transactionId, $amount, $appointmentId]);

    if ($updateStmt->rowCount() > 0) {
        $pdo->commit();

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

        // Redirect to success page
        header('Location: mock-success.php');
        exit;
    } else {
        $pdo->rollBack();
        die('Failed to update appointment. It may have expired.');
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Database Error: ' . $e->getMessage());
}
