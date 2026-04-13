<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$oldEmail = $_SESSION['old_email'] ?? '';
unset($_SESSION['errors'], $_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - ذات للاستشارات النفسية</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="./img/Silver.png" href="img/Silver.png">
    
    <link rel="stylesheet" href="../../therapist/total.css">
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
                <a href="../../homepage/index.php">
  <img src="img/Frame 392 (1).png" alt="شعار ذات" class="brand-icon">
</a>
         
           
        </div>
    </div>

    
    <div class="header-left">
        <a href="../login/index.php" class="nav-link active-login">تسجيل الدخول</a>
        <a href="../signup/index.php" class="nav-link active-login btn btn-primary">إنشاء حساب</a>
    </div>
</header>

<main class="page">
    <section class="login-section">
        <div class="card">
            <h1 class="login-title">تسجيل الدخول</h1>
            <p class="login-subtitle">مرحباً بك في منصة ذات للاستشارات النفسية</p>

            <form class="login-form" action="../handlers/login.php" method="post">
             
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input required id="email" name="email" type="text" class="form-input <?= isset($errors['email']) ? 'input-error' : '' ?>" placeholder="أدخل البريد الإلكتروني" value="<?= htmlspecialchars($oldEmail) ?>">
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

              
                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <div class="password-field">
                        <input id="password" name="password" type="password" class="form-input <?= isset($errors['password']) ? 'input-error' : '' ?>" placeholder="أدخل كلمة المرور" required>
                        <button type="button" class="toggle-password" aria-label="إظهار أو إخفاء كلمة المرور">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                </div>


                <div class="form-footer">
                    <a href="../forget-password/index.php" class="forgot-link">نسيت كلمة المرور؟</a>
                       
                </div>

                
                <button type="submit" class="btn  btn-block login-btn">
                    تسجيل الدخول
                </button>

              <p class="login-hint">
    ليس لديك حساب؟
    <a href="../signup/index.php" class="link">إنشاء حساب جديد</a>
</p>
            </form>
        </div>
    </section>
</main>

<footer class="main-footer">
    © 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
</footer>

<script src="main.js"></script>
</body>
</html>