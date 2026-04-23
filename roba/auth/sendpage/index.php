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
  <title>إنشاء حساب</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link
    href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap"
    rel="stylesheet"
  />
  <link rel="icon" type="image/png" href="img/Silver.png">

  <link rel="stylesheet" href="../../therapist/root.css">
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

      <?php if (!empty($errors)): ?>
        <div class="error-box">
          <?php foreach ($errors as $error): ?>
            <span class="error-message"><?= htmlspecialchars($error) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form id="surveyForm" action="../handlers/signup.php" method="POST">

        <!-- Hidden survey fields (populated from sessionStorage) -->
        <input type="hidden" name="survey_treatment_type" id="survey_treatment_type" />
        <input type="hidden" name="survey_symptoms" id="survey_symptoms" />
        <input type="hidden" name="survey_repeated_symptoms" id="survey_repeated_symptoms" />
        <input type="hidden" name="survey_prev_therapy" id="survey_prev_therapy" />
        <input type="hidden" name="survey_age" id="survey_age" />
        <input type="hidden" name="survey_gender" id="survey_gender" />
        <input type="hidden" name="survey_nationality" id="survey_nationality" />
        <input type="hidden" name="survey_therapist_gender" id="survey_therapist_gender" />
        <input type="hidden" name="survey_family_history" id="survey_family_history" />
        <input type="hidden" name="survey_physical_issues" id="survey_physical_issues" />
        <input type="hidden" name="survey_physical_details" id="survey_physical_details" />
        <input type="hidden" name="survey_marital_status" id="survey_marital_status" />
        <input type="hidden" name="survey_education_level" id="survey_education_level" />
        <input type="hidden" name="survey_smoking" id="survey_smoking" />
        <input type="hidden" name="survey_alcohol" id="survey_alcohol" />
        <input type="hidden" name="survey_drugs" id="survey_drugs" />
        <input type="hidden" name="survey_contact_preference" id="survey_contact_preference" />

        <!-- Hidden client preferences (required by handler; defaults applied) -->
        <input type="hidden" name="treatment_type" id="treatment_type" value="INDIVIDUAL_THERAPY" />
        <input type="hidden" name="session_type" value="BOTH" />
        <input type="hidden" name="session_time" value="FLEXIBLE" />

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
          <button type="submit" id="submitBtn" class="btn primary">إنشاء حساب</button>
        </div>
      </form>

      <p class="login-hint">
        لديك حساب بالفعل؟ <a href="../login/index.php" class="link">تسجيل الدخول</a>
      </p>
    </section>
  </main>


  <footer class="main-footer">
    &copy; 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const form = document.getElementById("surveyForm");
      const submitBtn = document.getElementById("submitBtn");

      const surveyRaw = sessionStorage.getItem("surveyData");
      if (surveyRaw) {
        try {
          const survey = JSON.parse(surveyRaw);
          const fieldMap = {
            treatment_type: "survey_treatment_type",
            symptoms: "survey_symptoms",
            repeated_symptoms: "survey_repeated_symptoms",
            prev_therapy: "survey_prev_therapy",
            age: "survey_age",
            gender: "survey_gender",
            nationality: "survey_nationality",
            therapist_gender: "survey_therapist_gender",
            family_history: "survey_family_history",
            physical_issues: "survey_physical_issues",
            physical_details: "survey_physical_details",
            marital_status: "survey_marital_status",
            education_level: "survey_education_level",
            smoking: "survey_smoking",
            alcohol: "survey_alcohol",
            drugs: "survey_drugs",
            contact_preference: "survey_contact_preference"
          };

          for (const [key, id] of Object.entries(fieldMap)) {
            const el = document.getElementById(id);
            if (el && survey[key] !== undefined) {
              el.value = survey[key];
            }
          }

          if (survey.treatment_type) {
            const tt = document.getElementById("treatment_type");
            if (tt) tt.value = survey.treatment_type;
          }
        } catch (e) {
          console.error("Error parsing survey data:", e);
        }
      }

      form.addEventListener("submit", function (e) {
        const password = document.getElementById("password").value;
        const confirm = document.getElementById("confirmPassword").value;

        if (password.length < 8) {
          e.preventDefault();
          alert("كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.");
          return;
        }

        if (password !== confirm) {
          e.preventDefault();
          alert("كلمتا المرور غير متطابقتين.");
          return;
        }

        if (!document.getElementById("survey_gender").value) {
          e.preventDefault();
          alert("يرجى إكمال الاستبيان أولاً قبل إنشاء الحساب.");
          window.location.href = "../signup/index.php";
        }
      });
    });
  </script>
</body>
</html>
