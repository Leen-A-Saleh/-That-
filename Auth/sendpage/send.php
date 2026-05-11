<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';

start_secure_session();
redirect('./index.php');
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
  <link rel="icon" type="./img/Silver.png" href="../img/Silver.png">
 
  <link rel="stylesheet" href="../../total.css">
 <link rel="stylesheet" href="./style.css" />
  <link rel="stylesheet" href="../../total.css" />
   <script src="../../total.js"></script>
</head>


<body>

  <header class="main-header">
    <div class="header-right">
        <div class="brand">
                 <a href="../../Homepage/index.php">
  <img src="../img/Frame 392 (1).png" alt="شعار ذات" class="brand-icon">
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
      <?php if ($errors !== []): ?>
        <div style="background:#ffe8e8;color:#8b0000;padding:12px;border-radius:10px;margin-bottom:14px;">
          <?= e(implode(' ', $errors)) ?>
        </div>
      <?php endif; ?>
      <p class="step-label">
        خطوة <span id="currentStep">1</span> من
        <span id="totalSteps">3</span>
      </p>

      <div class="wizard-progress">
        <div class="wizard-progress-bar" id="progressBar"></div>
      </div>

      <form id="surveyForm" method="post" action="">
        <?= csrf_input() ?>

        <div class="form-step active">
          <h2 class="step-title">المعلومات الشخصية</h2>

          <div class="field-group">
            <label class="field-label" for="fullName">الاسم الكامل</label>
            <input
              id="fullName"
              name="fullName"
              type="text"
              class="text-input"
              placeholder="أدخل اسمك الكامل"
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
              required
            />
          </div>
        </div>

        <div class="form-step">
          <h2 class="step-title">ما هو نوع الجلسة المفضل؟</h2>

          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="session_type" value="ONLINE" required />
              <span>جلسات عن بعد</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_type" value="IN_PERSON" />
              <span>جلسات حضورية</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_type" value="BOTH" />
              <span>كلاهما</span>
            </label>
          </div>

          <h2 class="step-title">ما هو الوقت المفضل للجلسات؟</h2>
          <div class="field-group time-grid">
            <label class="option-line">
              <input type="radio" name="session_time" value="MORNING" required />
              <span>صباحاً</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_time" value="AFTERNOON" />
              <span>ظهراً</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_time" value="EVENING" />
              <span>مساءً</span>
            </label>
            <label class="option-line">
              <input type="radio" name="session_time" value="FLEXIBLE" />
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
      </form>
      <div class="wizard-buttons">
        <button type="button" id="prevBtn" class="btn secondary" disabled>السابق</button>
        <button type="button" id="nextBtn" class="btn primary">التالي</button>
      </div>

        <p class="login-hint">
    لديك حساب بالفعل؟ <a href="../login/index.php" class="link">تسجيل الدخول</a>
</p>
    </section>
  </main>

  
  <footer class="main-footer">
    © 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
  </footer>

      <script>
        document.addEventListener("DOMContentLoaded", function () {
          const form = document.getElementById("surveyForm");
          const steps = Array.from(document.querySelectorAll(".form-step"));
          const currentStepEl = document.getElementById("currentStep");
          const totalStepsEl = document.getElementById("totalSteps");
          const progressBar = document.getElementById("progressBar");
          const prevBtn = document.getElementById("prevBtn");
          const nextBtn = document.getElementById("nextBtn");
          let current = 0;

          if (!form || !steps.length || !currentStepEl || !totalStepsEl || !progressBar || !prevBtn || !nextBtn) {
            return;
          }

          totalStepsEl.textContent = String(steps.length);

          function showStep(index) {
            steps.forEach((s, i) => s.classList.toggle("active", i === index));
            currentStepEl.textContent = String(index + 1);
            progressBar.style.width = (((index + 1) / steps.length) * 100) + "%";
            prevBtn.disabled = index === 0;
            nextBtn.textContent = index === steps.length - 1 ? "إنشاء الحساب" : "التالي";
          }

          function validateCurrentStep() {
            const step = steps[current];
            const requiredInputs = step.querySelectorAll("input[required]");
            for (const input of requiredInputs) {
              if (input.type === "radio") {
                const group = step.querySelectorAll(`input[type="radio"][name="${input.name}"]`);
                const checked = Array.from(group).some((r) => r.checked);
                if (!checked) return false;
              } else if (!input.value.trim()) {
                return false;
              }
            }
            return true;
          }

          prevBtn.addEventListener("click", function () {
            if (current > 0) {
              current -= 1;
              showStep(current);
            }
          });

          nextBtn.addEventListener("click", function () {
            if (!validateCurrentStep()) {
              alert("يرجى تعبئة جميع الحقول المطلوبة قبل المتابعة.");
              return;
            }

            if (current < steps.length - 1) {
              current += 1;
              showStep(current);
              return;
            }

            form.submit();
          });

          showStep(current);
        });
      </script>
</body>
</html>
