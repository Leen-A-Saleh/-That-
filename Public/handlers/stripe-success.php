<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/auth.php';

start_secure_session();

if (!is_authenticated() || ($_SESSION['auth']['role'] ?? '') !== 'CLIENT') {
    die('Unauthorized');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نجاح الدفع</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f3f4f6; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        .icon { color: #10b981; font-size: 48px; margin-bottom: 20px; }
        h1 { color: #111827; margin-bottom: 10px; font-size: 24px; }
        p { color: #6b7280; margin-bottom: 20px; }
        a { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; }
        a:hover { background-color: #1d4ed8; }
    </style>
    <script>
        // Redirect automatically after 5 seconds
        setTimeout(() => {
            window.location.href = '../../Client/client-appointments-page/appointments.php';
        }, 5000);
    </script>
</head>
<body>
    <div class="card">
        <div class="icon">✓</div>
        <h1>تمت عملية الدفع بنجاح</h1>
        <p>جاري تأكيد الموعد عبر نظامنا. قد يستغرق التحديث والإشعارات بضع لحظات حتى يكتمل.</p>
        <a href="../../Client/client-appointments-page/appointments.php">العودة إلى المواعيد</a>
    </div>
</body>
</html>
