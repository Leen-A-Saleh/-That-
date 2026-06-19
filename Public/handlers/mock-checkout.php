<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/auth.php';

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
    SELECT a.*, p.amount, u.name as therapist_name 
    FROM appointments a
    LEFT JOIN payments p ON (a.client_id = p.client_id AND a.therapist_id = p.therapist_id)
    LEFT JOIN users u ON a.therapist_id = u.user_id
    WHERE a.appointment_id = ? AND a.client_id = ? AND a.status = 'AWAITING_PAYMENT'
    ORDER BY p.created_at DESC
    LIMIT 1
");
$stmt->execute([$appointmentId, $clientId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die('Appointment not found or not awaiting payment.');
}

$amount = (float) ($appointment['amount'] ?? 150.00);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الدفع - إتمام الحجز</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .checkout-container { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 450px; width: 100%; box-sizing: border-box; }
        h1 { font-size: 24px; color: #111827; margin-top: 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 20px; }
        .order-summary { background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #e5e7eb; }
        .order-summary p { margin: 8px 0; color: #4b5563; font-size: 15px; }
        .order-summary span { font-weight: bold; color: #111827; }
        .price { font-size: 18px; color: #059669; font-weight: bold; margin-top: 10px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #374151; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-size: 15px; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        
        button { width: 100%; padding: 12px; background-color: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background-color 0.2s; margin-top: 10px; }
        button:hover { background-color: #1d4ed8; }
        
        .cancel-link { display: block; text-align: center; margin-top: 15px; color: #6b7280; text-decoration: none; font-size: 14px; }
        .cancel-link:hover { color: #111827; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h1>إتمام الدفع</h1>
        
        <div class="order-summary">
            <p>الأخصائي: <span><?= htmlspecialchars($appointment['therapist_name']) ?></span></p>
            <p>تاريخ الموعد: <span dir="ltr"><?= htmlspecialchars($appointment['date_time']) ?></span></p>
            <p>النوع: <span><?= $appointment['mode'] === 'ONLINE' ? 'عن بعد' : 'حضوري' ?></span></p>
            <p class="price">المبلغ الإجمالي: <?= number_format($amount, 2) ?> ر.س</p>
        </div>

        <form action="mock-process.php" method="POST">
            <input type="hidden" name="appointment_id" value="<?= $appointmentId ?>">
            <input type="hidden" name="amount" value="<?= $amount ?>">
            
            <div class="form-group">
                <label for="card_name">الاسم على البطاقة</label>
                <input type="text" id="card_name" name="card_name" required placeholder="مثال: Ahmad Ali">
            </div>
            
            <div class="form-group">
                <label for="card_number">رقم البطاقة</label>
                <input type="text" id="card_number" name="card_number" required placeholder="0000 0000 0000 0000" maxlength="19">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="expiry">تاريخ الانتهاء</label>
                    <input type="text" id="expiry" name="expiry" required placeholder="MM/YY" maxlength="5">
                </div>
                <div class="form-group">
                    <label for="cvv">رمز التحقق (CVV)</label>
                    <input type="password" id="cvv" name="cvv" required placeholder="123" maxlength="4">
                </div>
            </div>
            
            <button type="submit">دفع <?= number_format($amount, 2) ?> ر.س</button>
        </form>
        
        <a href="../../Client/client-appointments-page/appointments.php" class="cancel-link">العودة وإلغاء الدفع</a>
    </div>
</body>
</html>
