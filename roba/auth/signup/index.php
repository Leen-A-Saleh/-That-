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

<link rel="stylesheet" href="../../therapist/total.css">
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
    <a href="./signup.html" class="nav-link">انشاء حساب جديد</a>
</header>
<main>
  <div class="wizard-wrapper">
    <div class="card">
      <h1 class="card-title">استبيان لاختيار معالج نفسي مناسب</h1>

      <p class="step-label">
        خطوة <span id="currentStep">1</span> من
        <span id="totalSteps">1</span>
      </p>

      <div class="progress">
        <div class="progress-bar" id="progressBar"></div>
      </div>

      <form id="surveyForm">

        <!-- Step 1: treatment_type -->
        <div class="form-step active">
          <h2 class="step-title">نوع العلاج</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="treatment_type" value="علاج فردي للبالغين" />
              علاج فردي للبالغين
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="علاج فردي للأطفال" />
              علاج فردي للأطفال
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="إرشاد والدي" />
              إرشاد والدي
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="علاج زواجي" />
              علاج زواجي (الزوج والزوجة معًا)
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="العلاج الجماعي" />
              العلاج الجماعي
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="التقييم النفسي للبالغين" />
              التقييم النفسي للبالغين
            </label>
            <label class="option-line">
              <input type="radio" name="treatment_type" value="التقييم النفسي للأطفال" />
              التقييم النفسي للأطفال
            </label>
          </div>
        </div>

        <!-- Step 2: symptoms -->
        <div class="form-step">
          <h2 class="step-title">ما الأعراض التي تلاحظها؟</h2>
          <p class="field-hint">يمكن اختيار أكثر من خيار</p>
          <div class="field-group">
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="حزن" /> حزن
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="قلق" /> قلق
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="غضب" /> غضب
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="نوبات هلع" />
              نوبات هلع
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="تقلب مزاج" />
              تقلب مزاج
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="أفكار سلبية متكررة" />
              أفكار سلبية متكررة
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="صعوبة تركيز" />
              صعوبة تركيز
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="وساوس مزعجة" />
              وساوس مزعجة
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="مشاكل في النوم" />
              مشاكل في النوم
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="مشاكل في الأكل" />
              مشاكل في الأكل
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="آلام جسدية متكررة" />
              آلام جسدية متكررة (صداع، آلام معدة) دون وجود أسباب عضوية
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="صعوبة تكوين علاقات اجتماعية" />
              صعوبة تكوين علاقات اجتماعية
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="مشاكل عائلية مستمرة" />
              مشاكل مع شخص من أفراد العائلة بشكل مستمر
            </label>
            <label class="option-line">
              <input type="checkbox" name="symptoms[]" value="أخرى" /> أخرى
            </label>
          </div>
        </div>

        <!-- Step 3: repeated_symptoms -->
        <div class="form-step">
          <h2 class="step-title">هل تشعر مؤخرًا بتكرار أي من هذه الأعراض؟</h2>
          <p class="field-hint">يمكن اختيار أكثر من خيار</p>
          <div class="field-group">
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="مشاكل في النوم" />
              مشاكل في النوم
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="العصبية والانفعالات المتكررة" />
              العصبية والانفعالات المتكررة
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="البكاء المستمر" />
              البكاء المستمر
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="الحزن الدائم والمزاج المنخفض" />
              الحزن الدائم والمزاج المنخفض
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="الرغبة بالعزلة" />
              الرغبة بالعزلة
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="مشاكل في الشهية" />
              مشاكل في الشهية (أكل كميات أكبر/أقل من المعتاد)
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="إرهاق وتعب مزمن" />
              إرهاق وتعب مزمن
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="فقدان الرغبة بالاستمتاع بأي شيء" />
              فقدان الرغبة بالاستمتاع بأي شيء
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="سلوك إدماني" />
              سلوك إدماني للمواد المخدرة أو الكحول
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="مشاكل في العلاقات الاجتماعية" />
              مشاكل في العلاقات الاجتماعية
            </label>
            <label class="option-line">
              <input type="checkbox" name="repeated_symptoms[]" value="لا شيء مما ذكر" />
              لا شيء مما ذكر
            </label>
          </div>
        </div>

        <!-- Step 4: prev_therapy -->
        <div class="form-step">
          <h2 class="step-title">هل تلقيت علاجًا نفسيًا سابقًا؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="prev_therapy" value="YES" /> نعم
            </label>
            <label class="option-line">
              <input type="radio" name="prev_therapy" value="NO" /> لا
            </label>
          </div>
        </div>

        <!-- Step 5: age -->
        <div class="form-step">
          <h2 class="step-title">ما هو عمرك؟</h2>
          <div class="field-group">
            <label class="field-label">العمر</label>
            <input
              type="number"
              name="age"
              class="text-input"
              min="0"
              placeholder="أدخل عمرك"
            />
          </div>
        </div>

        <!-- Step 6: gender -->
        <div class="form-step">
          <h2 class="step-title">ما هو جنسك؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="gender" value="FEMALE" /> أنثى
            </label>
            <label class="option-line">
              <input type="radio" name="gender" value="MALE" /> ذكر
            </label>
          </div>
        </div>

        <!-- Step 7: nationality -->
        <div class="form-step">
          <h2 class="step-title">ما هي جنسيتك؟</h2>
          <div class="field-group">
            <label class="field-label">الجنسية</label>
            <input
              type="text"
              name="nationality"
              class="text-input"
              placeholder="اكتب جنسيتك"
            />
          </div>
        </div>

        <!-- Step 8: therapist_gender -->
        <div class="form-step">
          <h2 class="step-title">ما هو الجنس المفضّل للمعالج؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="therapist_gender" value="MALE" /> ذكر
            </label>
            <label class="option-line">
              <input type="radio" name="therapist_gender" value="FEMALE" /> أنثى
            </label>
            <label class="option-line">
              <input type="radio" name="therapist_gender" value="NO_PREFERENCE" />
              لا يهم
            </label>
          </div>
        </div>

        <!-- Step 9: family_history -->
        <div class="form-step">
          <h2 class="step-title">
            هل يوجد تاريخ عائلي لأي مشاكل نفسية أو عقلية؟
          </h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="family_history" value="YES" /> نعم
            </label>
            <label class="option-line">
              <input type="radio" name="family_history" value="NO" /> لا
            </label>
          </div>
        </div>

        <!-- Step 10: physical_issues -->
        <div class="form-step">
          <h2 class="step-title">هل تعاني من مشاكل جسدية صحية؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="physical_issues" value="YES" /> نعم
            </label>
            <label class="option-line">
              <input type="radio" name="physical_issues" value="NO" /> لا
            </label>
          </div>
          <div class="field-group">
            <label class="field-label">إذا كانت الإجابة نعم، يرجى التحديد</label>
            <input
              type="text"
              name="physical_details"
              class="text-input"
              placeholder="اكتب أي مشاكل جسدية إن وجدت"
            />
          </div>
        </div>

        <!-- Step 11: marital_status -->
        <div class="form-step">
          <h2 class="step-title">ما هي حالتك الاجتماعية؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="marital_status" value="SINGLE" />
              أعزب/عزباء
            </label>
            <label class="option-line">
              <input type="radio" name="marital_status" value="MARRIED" />
              متزوج/ـة
            </label>
            <label class="option-line">
              <input type="radio" name="marital_status" value="WIDOWED" />
              أرمل/ـة
            </label>
            <label class="option-line">
              <input type="radio" name="marital_status" value="DIVORCED" />
              مطلق/ـة
            </label>
            <label class="option-line">
              <input type="radio" name="marital_status" value="IN_RELATIONSHIP" />
              مرتبط/ـة
            </label>
            <label class="option-line">
              <input type="radio" name="marital_status" value="SEPARATED" />
              منفصل/ـة
            </label>
            <label class="option-line">
              <input type="radio" name="marital_status" value="PREFER_NOT_TO_SAY" />
              أفضل عدم الإجابة
            </label>
          </div>
        </div>

        <!-- Step 12: education_level -->
        <div class="form-step">
          <h2 class="step-title">ما هو مستواك التعليمي؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="education_level" value="LESS_THAN_HIGH_SCHOOL" />
              أقل من الثانوية
            </label>
            <label class="option-line">
              <input type="radio" name="education_level" value="HIGH_SCHOOL" />
              ثانوية عامة
            </label>
            <label class="option-line">
              <input type="radio" name="education_level" value="BACHELOR" />
              بكالوريوس
            </label>
            <label class="option-line">
              <input type="radio" name="education_level" value="MASTER" /> ماستر
            </label>
            <label class="option-line">
              <input type="radio" name="education_level" value="PHD" />
              دكتوراه
            </label>
            <label class="option-line">
              <input type="radio" name="education_level" value="OTHER" /> أخرى
            </label>
          </div>
        </div>

        <!-- Step 13: smoking -->
        <div class="form-step">
          <h2 class="step-title">هل تدخن؟</h2>
          <p class="field-hint">
            جميع الإجابات سرّية وتُستخدم فقط لدعم التقييم والعلاج
          </p>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="smoking" value="YES" /> نعم
            </label>
            <label class="option-line">
              <input type="radio" name="smoking" value="NO" /> لا
            </label>
          </div>
        </div>

        <!-- Step 14: alcohol -->
        <div class="form-step">
          <h2 class="step-title">هل تتناول المشروبات الكحولية؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="alcohol" value="YES" /> نعم
            </label>
            <label class="option-line">
              <input type="radio" name="alcohol" value="NO" /> لا
            </label>
          </div>
        </div>

        <!-- Step 15: drugs -->
        <div class="form-step">
          <h2 class="step-title">هل تتعاطى أي نوع من المخدرات؟</h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="drugs" value="YES" /> نعم
            </label>
            <label class="option-line">
              <input type="radio" name="drugs" value="NO" /> لا
            </label>
          </div>
        </div>

        <!-- Step 16: contact_preference -->
        <div class="form-step">
          <h2 class="step-title">
            كيف تحب أن يتواصل معك فريق خدمة العملاء؟
          </h2>
          <div class="field-group">
            <label class="option-line">
              <input type="radio" name="contact_preference" value="WHATSAPP" />
              الواتساب
            </label>
            <label class="option-line">
              <input type="radio" name="contact_preference" value="EMAIL" />
              البريد الإلكتروني
            </label>
          </div>
        </div>
      </form>

        <!-- أزرار التنقل -->
        <div class="nav-buttons">
          <button type="button" id="prevBtn" class="btn prev" disabled>
            السابق
          </button>
          <button type="button" id="nextBtn" class="btn next">التالي</button>
        </div>
      </div>
    </div>
  </main>

  <footer class="main-footer">
    © 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
</footer>

  <script src="main.js"></script>
</body>
</html>
