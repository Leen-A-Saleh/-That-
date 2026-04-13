document.addEventListener("DOMContentLoaded", function () {
  const steps = document.querySelectorAll(".form-step");
  const currentStepEl = document.getElementById("currentStep");
  const totalStepsEl = document.getElementById("totalSteps");
  const progressBar = document.getElementById("progressBar");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const form = document.getElementById("surveyForm");

  // لو ما قدر يحصل أي عنصر مهم، يوقف
  if (
    !steps.length ||
    !currentStepEl ||
    !totalStepsEl ||
    !progressBar ||
    !prevBtn ||
    !nextBtn ||
    !form
  ) {
    console.error("بعض عناصر النموذج غير موجودة في الـ HTML.");
    return;
  }

  let currentIndex = 0;
  const total = steps.length;

  // عرض العدد الكلي للخطوات
  totalStepsEl.textContent = total;

  function showStep(index) {
    // إظهار/إخفاء الخطوات
    steps.forEach((step, i) => {
      step.classList.toggle("active", i === index);
    });

    // تحديث رقم الخطوة
    currentStepEl.textContent = index + 1;

    // تحديث شريط التقدم
    const percent = ((index + 1) / total) * 100;
    progressBar.style.width = percent + "%";

    // تفعيل/تعطيل زر السابق
    prevBtn.disabled = index === 0;

    // تغيير نص زر التالي في آخر خطوة
    nextBtn.textContent = index === total - 1 ? "إرسال" : "التالي";
  }

  function validateCurrentStep() {
    const step = steps[currentIndex];

    // التحقق من أسئلة الراديو في هذه الخطوة
    const radios = step.querySelectorAll('input[type="radio"]');
    if (radios.length > 0) {
      const names = new Set();
      radios.forEach((r) => names.add(r.name));

      for (const name of names) {
        const group = step.querySelectorAll(
          'input[type="radio"][name="' + name + '"]'
        );
        const checked = Array.from(group).some((r) => r.checked);
        if (!checked) {
          alert("رجاءً اختر إجابة لهذا السؤال قبل المتابعة.");
          group[0].focus();
          return false;
        }
      }
    }

    // التحقق من وجود على الأقل تشيك بوكس واحد لو فيه تشيك بوكس في هذه الخطوة
    const checkboxes = step.querySelectorAll('input[type="checkbox"]');
    if (checkboxes.length > 0) {
      const anyChecked = Array.from(checkboxes).some((c) => c.checked);
      if (!anyChecked) {
        alert("رجاءً اختر خياراً واحداً على الأقل قبل المتابعة.");
        checkboxes[0].focus();
        return false;
      }
    }

    // التحقق من الحقول النصية/الرقمية في هذه الخطوة
    const textInputs = step.querySelectorAll(
      'input[type="text"], input[type="number"], input[type="email"], input[type="tel"], textarea'
    );

    for (const input of textInputs) {
      const name = input.name;
      const value = input.value.trim();

      // حقل تفاصيل المشاكل الجسدية: مطلوب فقط إذا اختار المستخدم "نعم"
      if (name === "physical_details") {
        const physicalIssues = form.querySelector(
          'input[name="physical_issues"]:checked'
        );
        if (!physicalIssues || physicalIssues.value !== "yes") {
          continue;
        }
      }

      if (value === "") {
        alert("رجاءً املأ الحقل قبل المتابعة.");
        input.focus();
        return false;
      }
    }

    return true;
  }

  // زر التالي / إرسال
  nextBtn.addEventListener("click", () => {
    if (!validateCurrentStep()) return;

    if (currentIndex < total - 1) {
      currentIndex++;
      showStep(currentIndex);
    } else {
      // هذه آخر خطوة: إرسال النموذج والانتقال
      const formData = new FormData(form);
      console.log("Form data:");
      for (const [name, value] of formData.entries()) {
        console.log(name, "=>", value);
      }

      alert("تم إرسال الاستبيان بنجاح، شكرًا لمشاركتك!");
  window.location.href = "../sendpage/index.php";

      // الانتقال لصفحة الإرسال
    
    }
  });

  // زر السابق
  prevBtn.addEventListener("click", () => {
    if (currentIndex > 0) {
      currentIndex--;
      showStep(currentIndex);
    }
  });

  // تعطيل/تفعيل حقل تفاصيل المشاكل الجسدية حسب اختيار المستخدم
  const physicalIssuesRadios = form.querySelectorAll(
    'input[name="physical_issues"]'
  );
  const physicalDetailsInput = form.querySelector(
    'input[name="physical_details"]'
  );

  function updatePhysicalDetailsState() {
    if (!physicalDetailsInput) return;
    const selected = form.querySelector('input[name="physical_issues"]:checked');
    if (selected && selected.value === "no") {
      physicalDetailsInput.disabled = true;
      physicalDetailsInput.value = "";
    } else {
      physicalDetailsInput.disabled = false;
    }
  }

  physicalIssuesRadios.forEach((radio) => {
    radio.addEventListener("change", updatePhysicalDetailsState);
  });
  updatePhysicalDetailsState();

  // إظهار أول خطوة عند تحميل الصفحة
  showStep(currentIndex);
});