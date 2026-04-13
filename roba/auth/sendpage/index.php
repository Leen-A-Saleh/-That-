<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>استبيان لاختيار معالج نفسي مناسب</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link
    href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap"
    rel="stylesheet"
  />
  <link rel="icon" type="./img/Silver.png" href="img/Silver.png">
 
      <link  rel="stylesheet" href="../../therapist/root.css">
    <link rel="stylesheet" href="../../therapist/total.css">  
 <link rel="stylesheet" href="./style.css" />
 
   <script src="../../therapist/total.js"></script>
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
        <a href="../login/index.php" class="nav-link">تسجيل الدخول</a>
       <a href="../signup/index.php" class="btn btn-primary" id="signupLink">
        إنشاء حساب
    </a>
    </div>
</header>

  
  <main class="page-main">
    <section class="card">
      <p class="step-label">
        خطوة <span id="currentStep">1</span> من
        <span id="totalSteps">4</span>
      </p>

      <div class="wizard-progress">
        <div class="wizard-progress-bar" id="progressBar"></div>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="error-box">
          <?php foreach ($errors as $error): ?>
            <span class="error-message"><?= htmlspecialchars($error) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form id="surveyForm" action="../handlers/signup.php" method="POST">

        <div class="form-step active">
          <h2 class="step-title">ما نوع العلاج الذي تبحث عنه؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="treatment_type" value="علاج فردي" required <?= ($old['treatment_type'] ?? '') === 'علاج فردي' ? 'checked' : '' ?> />
              <span>علاج فردي</span>
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="علاج زوجي" <?= ($old['treatment_type'] ?? '') === 'علاج زوجي' ? 'checked' : '' ?> />
              <span>علاج زوجي</span>
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="علاج سلوكي للأطفال والمراهقين" <?= ($old['treatment_type'] ?? '') === 'علاج سلوكي للأطفال والمراهقين' ? 'checked' : '' ?> />
              <span>علاج سلوكي للأطفال والمراهقين</span>
            </label>
          </div>
        </div>

        <div class="form-step ">
          <h2 class="step-title">المعلومات الشخصية</h2>

          <div class="field-group">
            <label class="field-label" for="fullName">الاسم الكامل</label>
            <input
              id="fullName"
              name="fullName"
              type="text"
              class="text-input"
              placeholder="أدخل اسمك الكامل"
              value="<?= htmlspecialchars($old['fullName'] ?? '') ?>"
              required
            />
          </div>

          <div class="field-group">
            <label class="field-label" for="email">البريد الإلكتروني</label>
            <input
              id="email"
              name="email"
              type="email"
              class="text-input"
              placeholder="example@email.com"
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
              required
            />
          </div>

          <div class="field-group">
            <label class="field-label" for="phone">رقم الهاتف</label>
            <input
              id="phone"
              name="phone"
              type="tel"
              class="text-input"
              placeholder="05xxxxxxxx"
              value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
              required
            />
          </div>
        </div>

        <div class="form-step">
          <h2 class="step-title">ما هو نوع الجلسة المفضل؟</h2>

          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="session_type" value="جلسات عن بعد" required <?= ($old['session_type'] ?? '') === 'جلسات عن بعد' ? 'checked' : '' ?> />
              <span>جلسات عن بعد</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_type" value="جلسات حضورية" <?= ($old['session_type'] ?? '') === 'جلسات حضورية' ? 'checked' : '' ?> />
              <span>جلسات حضورية</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_type" value="كلاهما" <?= ($old['session_type'] ?? '') === 'كلاهما' ? 'checked' : '' ?> />
              <span>كلاهما</span>
            </label>
          </div>

          <h2 class="step-title">ما هو الوقت المفضل للجلسات؟</h2>
          <div class="field-group time-grid">
            <label class="option-line">
              <input type="radio" name="session_time" value="صباحاً" required <?= ($old['session_time'] ?? '') === 'صباحاً' ? 'checked' : '' ?> />
              <span>صباحاً</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_time" value="ظهراً" <?= ($old['session_time'] ?? '') === 'ظهراً' ? 'checked' : '' ?> />
              <span>ظهراً</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_time" value="مساءً" <?= ($old['session_time'] ?? '') === 'مساءً' ? 'checked' : '' ?> />
              <span>مساءً</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_time" value="مرن" <?= ($old['session_time'] ?? '') === 'مرن' ? 'checked' : '' ?> />
              <span>مرن</span>
            </label>
          </div>
        </div>

        <div class="form-step">
          <h2 class="step-title">إنشاء حساب</h2>
          <div class="field-group">
            <label class="field-label" for="username">اسم المستخدم</label>
            <input
              id="username"
              name="username"
              type="text"
              class="text-input"
              placeholder="اختر اسم مستخدم"
              value="<?= htmlspecialchars($old['username'] ?? '') ?>"
              required
            />
          </div>

          <div class="field-group">
            <label class="field-label" for="password">كلمة المرور</label>
            <input
              id="password"
              name="password"
              type="password"
              class="text-input"
              placeholder="أدخل كلمة المرور"
              required
            />
            <p class="field-hint">يجب أن تحتوي على 8 أحرف على الأقل</p>
          </div>

          <div class="field-group">
            <label class="field-label" for="confirmPassword">تأكيد كلمة المرور</label>
            <input
              id="confirmPassword"
              name="confirmPassword"
              type="password"
              class="text-input"
              placeholder="أعد إدخال كلمة المرور"
              required
            />
          </div>
        </div>
      <div class="wizard-buttons">
        <button type="button" id="prevBtn" class="btn secondary" disabled>السابق</button>
        <button type="button" id="nextBtn" class="btn primary">التالي</button>
        <button type="submit" id="submitBtn" class="btn primary" style="display:none;">إنشاء حساب</button>
      </div>
      </form>

        <p class="login-hint">
    لديك حساب بالفعل؟ <a href="../login/index.php" class="link">تسجيل الدخول</a>
</p>
    </section>
  </main>

  
  <footer class="main-footer">
    © 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
  </footer>

  <script src="main.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const nextBtn = document.getElementById("nextBtn");
      const submitBtn = document.getElementById("submitBtn");
      const form = document.getElementById("surveyForm");
      const steps = document.querySelectorAll(".form-step");
      const total = steps.length;

      // Override showStep to toggle next/submit buttons
      const observer = new MutationObserver(() => {
        const activeIndex = Array.from(steps).findIndex(s => s.classList.contains("active"));
        if (activeIndex === total - 1) {
          nextBtn.style.display = "none";
          submitBtn.style.display = "inline-block";
        } else {
          nextBtn.style.display = "inline-block";
          submitBtn.style.display = "none";
        }
      });

      steps.forEach(step => observer.observe(step, { attributes: true, attributeFilter: ["class"] }));

      // Validate last step and submit
      submitBtn.addEventListener("click", function () {
        const lastStep = steps[total - 1];
        const passwordInput = document.getElementById("password");
        const confirmInput = document.getElementById("confirmPassword");

        // Check required fields
        const inputs = lastStep.querySelectorAll("input[required]");
        for (const input of inputs) {
          if (input.value.trim() === "") {
            alert("رجاءً املأ جميع الحقول.");
            input.focus();
            return;
          }
        }

        if (passwordInput.value.length < 8) {
          alert("كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.");
          passwordInput.focus();
          return;
        }

        if (passwordInput.value !== confirmInput.value) {
          alert("كلمتا المرور غير متطابقتين.");
          confirmInput.focus();
          return;
        }

        form.submit();
      });
    });
  </script>
</body>
</html>