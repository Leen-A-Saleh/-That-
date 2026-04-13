<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نسيت كلمة المرور - ذات للاستشارات النفسية</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="./img/Silver.png" href="img/Silver.png">
    
   <link  rel="stylesheet" href="../../therapist/root.css">
    <link rel="stylesheet" href="../../therapist/total.css">
    <link rel="stylesheet" href="stayle.css">

   
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
        <a href="../signup/index.php" class="nav-link">انشاء حساب جديد</a>
   
    </div>
</header>
<main class="page-main">
  <section class="card">
  
    <img src="img/Background.png" alt="نسيت كلمة المرور" class="brando">

  
    <h1 class="card__title">نسيت كلمة المرور؟</h1>

  
    <p class="card__text">
      أدخل بريدك الإلكتروني، وسنرسل لك رابطًا لإعادة تعيين كلمة المرور
    </p>

    
    <?php if (isset($_GET['sent'])): ?>
      <div class="success-msg">تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form class="card__form" method="POST" action="../handlers/forget-password.php">
  <div class="card__form-row">
    <label for="email" class="card__label">البريد الإلكتروني</label>
    <input
      id="email"
      name="email"
      type="email"
      placeholder="example@email.com"
      required
    >
  </div>

  <button type="submit" class="card__submit">
    إرسال رابط إعادة التعيين
  </button>
</form>

    
    <a href="../login/index.php" class="linko">العودة إلى تسجيل الدخول</a>
    
  </section>
</main>

<footer class="main-footer">
    © 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
</footer>

 
<script src="main.js"></script>
</body>
</html>