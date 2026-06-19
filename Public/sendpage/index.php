<?php

declare(strict_types=1);
require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
start_secure_session();

// ─── Page Data ────────────────────────────────────────────────────────────────

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
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../img/Silver.png">

  <link rel="stylesheet" href="../../total.css">
  <link rel="stylesheet" href="./style.css" />
  <script src="../../total.js"></script>
  <!-- نظام التنبيهات SweetAlert2 + الدوال الموحّدة -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/sweet-alerts.js"></script>
</head>

<body>
  <header class="main-header">
    <div class="header-right">
      <div class="brand">
        <a href="/That-Copy/Public/homepage/index.php">
          <img src="/That-Copy/Client/images/Frame 393.svg" alt="شعار ذات" class="brand-icon">
        </a>
      </div>
    </div>
    <div class="header-left">
      <a href="../login/index.php" class="nav-link">تسجيل الدخول</a>
      <a href="../signup/index.php" class="btn btn-primary" id="signupLink">إنشاء حساب</a>
    </div>
  </header>

  <main class="page-main">
    <section class="card">

      <!-- أخطاء الخادم تُمرَّر إلى JavaScript لعرضها عبر SweetAlert بدل صندوق الأخطاء التقليدي -->
      <script>
        window.SERVER_ERRORS = <?= json_encode(array_values($errors), JSON_UNESCAPED_UNICODE) ?>;
      </script>

      <form id="surveyForm" action="../handlers/signup.php" method="POST" novalidate>
        <?= csrf_input() ?>

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
        <input type="hidden" name="survey_parental_consent" id="survey_parental_consent" />

        <input type="hidden" name="treatment_type" id="treatment_type" value="INDIVIDUAL_THERAPY" />
        <input type="hidden" name="session_type" value="BOTH" />
        <input type="hidden" name="session_time" value="FLEXIBLE" />

        <div class="form-step active">
          <h2 class="step-title">المعلومات الشخصية</h2>

          <div class="field-group">
            <label class="field-label" for="age">العمر</label>
            <input id="age" name="age" type="number" class="text-input" placeholder="أدخل عمرك" value="<?= e($old['age'] ?? '') ?>" min="0" required />
          </div>

          <div class="field-group">
            <label class="field-label">الجنس</label>
            <label class="option-line"><input type="radio" name="gender" value="M" <?= ($old['gender'] ?? '') === 'M' ? 'checked' : '' ?> required /> ذكر (M)</label>
            <label class="option-line"><input type="radio" name="gender" value="F" <?= ($old['gender'] ?? '') === 'F' ? 'checked' : '' ?> required /> أنثى (F)</label>
          </div>

          <div id="guardianSection" style="display: none;">
            <div class="field-group" style="display: none;">
              <label class="field-label">هل الأب أو ولي الأمر موجود؟</label>
              <label class="option-line"><input type="radio" name="has_guardian" value="1" <?= ($old['has_guardian'] ?? '1') === '1' ? 'checked' : '' ?> /> نعم</label>
              <label class="option-line"><input type="radio" name="has_guardian" value="0" <?= ($old['has_guardian'] ?? '') === '0' ? 'checked' : '' ?> /> لا</label>
            </div>
            
            <div id="guardianDetails" style="display: none;">
              <div class="field-group">
                <label class="field-label" for="guardian_name">اسم ولي الأمر بالكامل</label>
                <input id="guardian_name" name="guardian_name" type="text" class="text-input" placeholder="أدخل اسم ولي الأمر" value="<?= e($old['guardian_name'] ?? '') ?>" />
              </div>
              <div class="field-group">
                <label class="field-label" for="guardian_phone">رقم هاتف ولي الأمر</label>
                <input id="guardian_phone" name="guardian_phone" type="tel" class="text-input" placeholder="05xxxxxxxx" value="<?= e($old['guardian_phone'] ?? '') ?>" />
              </div>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label" for="fullName">الاسم الكامل</label>
            <input id="fullName" name="fullName" type="text" class="text-input" placeholder="أدخل اسمك الكامل" value="<?= e($old['fullName'] ?? '') ?>" required />
          </div>

          <div class="field-group">
            <label class="field-label" for="email">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" class="text-input" placeholder="example@email.com" value="<?= e($old['email'] ?? '') ?>" required />
          </div>

          <div class="field-group">
            <label class="field-label" for="phone">رقم الهاتف</label>
            <input id="phone" name="phone" type="tel" class="text-input" placeholder="05xxxxxxxx" value="<?= e($old['phone'] ?? '') ?>" required />
          </div>

          <h2 class="step-title">إنشاء حساب</h2>

          <div class="field-group">
            <label class="field-label" for="username">اسم المستخدم</label>
            <input id="username" name="username" type="text" class="text-input" placeholder="اختر اسم مستخدم" value="<?= e($old['username'] ?? '') ?>" required />
          </div>

          <div class="field-group">
            <label class="field-label" for="password">كلمة المرور</label>
            <input id="password" name="password" type="password" class="text-input" placeholder="أدخل كلمة المرور" required />
            <p class="field-hint">يجب أن تحتوي على 8 أحرف على الأقل</p>
          </div>

          <div class="field-group">
            <label class="field-label" for="confirmPassword">تأكيد كلمة المرور</label>
            <input id="confirmPassword" name="confirmPassword" type="password" class="text-input" placeholder="أعد إدخال كلمة المرور" required />
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
            contact_preference: "survey_contact_preference",
            parental_consent: "survey_parental_consent"
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

          // Pre-fill age and gender from survey if not already filled
          const ageInput = document.getElementById("age");
          if (survey.age && !ageInput.value) {
            ageInput.value = survey.age;
          }
          if (survey.gender && !document.querySelector('input[name="gender"]:checked')) {
            let mappedGender = survey.gender === "MALE" ? "M" : (survey.gender === "FEMALE" ? "F" : "");
            if (mappedGender) {
              let genderRadio = document.querySelector(`input[name="gender"][value="${mappedGender}"]`);
              if (genderRadio) genderRadio.checked = true;
            }
          }
        } catch (e) {
          console.error("Error parsing survey data:", e);
        }
      }

      // Logic for Guardian fields
      const ageInput = document.getElementById('age');
      const guardianSection = document.getElementById('guardianSection');
      const guardianDetails = document.getElementById('guardianDetails');
      const guardianRadios = document.querySelectorAll('input[name="has_guardian"]');
      const guardianName = document.getElementById('guardian_name');
      const guardianPhone = document.getElementById('guardian_phone');

      function updateGuardianVisibility() {
          const age = parseInt(ageInput.value, 10);
          if (!isNaN(age) && age < 18) {
              guardianSection.style.display = 'block';
              guardianDetails.style.display = 'block';
              let yesRadio = document.querySelector('input[name="has_guardian"][value="1"]');
              if (yesRadio) yesRadio.checked = true;
          } else {
              guardianSection.style.display = 'none';
              guardianDetails.style.display = 'none';
              let noRadio = document.querySelector('input[name="has_guardian"][value="0"]');
              if (noRadio) noRadio.checked = true;
          }
      }

      if (ageInput) {
          ageInput.addEventListener('input', updateGuardianVisibility);
      }
      guardianRadios.forEach(r => r.addEventListener('change', updateGuardianVisibility));
      updateGuardianVisibility();

      // عرض أخطاء الخادم (إن وُجدت) عبر SweetAlert بدل صندوق الأخطاء التقليدي
      if (Array.isArray(window.SERVER_ERRORS) && window.SERVER_ERRORS.length > 0) {
        showErrorAlert(window.SERVER_ERRORS.join("\n"));
      }

      // التحقق المخصّص عند الإرسال (بدون checkValidity على الفورم بالكامل)
      // الفورم يحمل novalidate لمنع رسائل المتصفح الافتراضية مثل "Please fill out this field".
      form.addEventListener("submit", function (e) {
        const fullName = document.getElementById("fullName").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value;
        const confirm = document.getElementById("confirmPassword").value;

        if (!fullName || !email || !phone || !username || !password || !confirm) {
          e.preventDefault();
          showErrorAlert("يرجى تعبئة جميع الحقول المطلوبة قبل إنشاء الحساب.");
          return;
        }
        if (password.length < 8) {
          e.preventDefault();
          showErrorAlert("كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.");
          return;
        }
        if (password !== confirm) {
          e.preventDefault();
          showErrorAlert("كلمتا المرور غير متطابقتين.");
          return;
        }
        
        const genderChecked = document.querySelector('input[name="gender"]:checked');
        if (!genderChecked) {
          e.preventDefault();
          showErrorAlert("يرجى تحديد الجنس.");
          return;
        }

        const ageVal = parseInt(ageInput.value, 10);
        if (!isNaN(ageVal) && ageVal < 18) {
          const hasGuardian = document.querySelector('input[name="has_guardian"]:checked');
          if (!hasGuardian || hasGuardian.value !== '1') {
            e.preventDefault();
            showErrorAlert("لا يمكن إكمال التسجيل لمن هم أقل من 18 سنة دون وجود ولي الأمر.");
            return;
          }
          if (!guardianName.value.trim() || !guardianPhone.value.trim()) {
            e.preventDefault();
            showErrorAlert("يرجى إدخال اسم ورقم هاتف ولي الأمر.");
            return;
          }
        }

        // كل التحققات نجحت → Toast نجاح قبل إرسال النموذج للخادم
        showSuccessToast("جارٍ إنشاء حسابك...");
      });
    });
  </script>
</body>
</html>
