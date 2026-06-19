<?php

declare(strict_types=1);
require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
start_secure_session();

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$validToken = false;

if ($token !== '' && $email !== '') {
    $validToken = validate_reset_token($token, $email);
}

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إعادة تعيين كلمة المرور - ذات للاستشارات النفسية</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" type="image/png" href="../img/Silver.png">

  <link rel="stylesheet" href="../../total.css">
  <link rel="stylesheet" href="style.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

  <header class="main-header">
    <div class="header-right">
      <div class="brand">
        <a href="../homepage/index.php">
          <img src="../img/Frame 392 (1).png" alt="شعار ذات" class="brand-icon">
        </a>
      </div>
    </div>

    <div class="header-left">
      <a href="../login/index.php" class="nav-link active-login">تسجيل الدخول</a>
      <a href="../signup/index.php" class="nav-link active-login btn btn-primary">إنشاء حساب</a>
    </div>
  </header>

<main class="page-main">
  <section class="card">

    <h1 class="card__title">إعادة تعيين كلمة المرور</h1>

    <?php if ($validToken): ?>

    <p class="card__text">أدخل كلمة المرور الجديدة</p>

    <?php if ($errors !== []): ?>
      <div class="error-msg"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>

    <form class="card__form" method="POST" action="../handlers/reset-password.php">
      <?= csrf_input() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <input type="hidden" name="email" value="<?= e($email) ?>">

      <div class="card__form-row">
        <label for="password" class="card__label">كلمة المرور الجديدة</label>
        <input id="password" name="password" type="password" placeholder="أدخل كلمة المرور الجديدة" required>
        <span class="error-message" id="password-error"></span>
      </div>

      <div class="card__form-row">
        <label for="confirm_password" class="card__label">تأكيد كلمة المرور</label>
        <input id="confirm_password" name="confirm_password" type="password" placeholder="أعد إدخال كلمة المرور" required>
        <span class="error-message" id="confirm-error"></span>
      </div>

      <button type="submit" class="card__submit">إعادة تعيين كلمة المرور</button>
    </form>

    <?php else: ?>

    <p class="card__text error-msg">الرابط غير صالح أو منتهي الصلاحية</p>

    <?php endif; ?>

    <a href="../login/index.php" class="linko">العودة إلى تسجيل الدخول</a>

  </section>
</main>

<footer class="main-footer">
    © 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
</footer>

<script src="main.js"></script>
</body>
</html>
