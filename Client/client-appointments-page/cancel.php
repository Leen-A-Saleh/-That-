<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/notifications-database.php';

start_secure_session();

header('Content-Type: application/json');

if (!is_authenticated() || ($_SESSION['auth']['role'] ?? '') !== 'CLIENT') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$clientId = (int) $_SESSION['auth']['user_id'];
$appointmentId = (int) ($_POST['appointment_id'] ?? 0);

if ($appointmentId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND client_id = ? LIMIT 1");
$stmt->execute([$appointmentId, $clientId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
    exit;
}

if ($appointment['status'] !== 'CONFIRMED' || $appointment['payment_status'] !== 'PAID') {
    echo json_encode(['success' => false, 'message' => 'لا يمكن إلغاء إلا المواعيد المؤكدة والمدفوعة.']);
    exit;
}

$paidAmount = (float) $appointment['paid_amount'];
$refundAmount = $paidAmount * 0.50; // 50% refund

if ($paidAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'بيانات الدفع غير صحيحة ولا يمكن استرداد المبلغ.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Mock Refund (No Stripe API call)

    $updateStmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'CANCELLED_BY_CLIENT', 
            payment_status = 'PARTIALLY_REFUNDED',
            refund_amount = ?,
            refund_completed_at = NOW()
        WHERE appointment_id = ? AND status = 'CONFIRMED'
    ");
    $updateStmt->execute([$refundAmount, $appointmentId]);

    $pdo->commit();

    create_user_notification(
        $clientId,
        'تم إلغاء الموعد',
        'تم إلغاء موعدك بنجاح واسترداد 50% من المبلغ.',
        'ALERT',
        'NORMAL'
    );

    create_user_notification(
        (int) $appointment['therapist_id'],
        'إلغاء موعد من قبل العميل',
        'قام العميل بإلغاء الموعد المؤكد.',
        'ALERT',
        'NORMAL'
    );

    echo json_encode(['success' => true, 'message' => 'تم إلغاء الموعد بنجاح واسترداد 50% من المبلغ المدفوع.']);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الإلغاء: ' . $e->getMessage()]);
}
