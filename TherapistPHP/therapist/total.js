document.addEventListener("DOMContentLoaded", function () {
  const steps = document.querySelectorAll(".form-step");
  const currentStepEl = document.getElementById("currentStep");
  const totalStepsEl = document.getElementById("totalSteps");
  const progressBar = document.getElementById("progressBar");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const form = document.getElementById("surveyForm");
  if (
    !steps.length ||
    !currentStepEl ||
    !totalStepsEl ||
    !progressBar ||
    !prevBtn ||
    !nextBtn ||
    !form
  ) {
    return;
  }
  let currentIndex = 0;
  const total = steps.length;
  totalStepsEl.textContent = total;
  function showStep(index) {
    steps.forEach((step, i) => {
      step.classList.toggle("active", i === index);
    });
    currentStepEl.textContent = index + 1;
    const percent = ((index + 1) / total) * 100;
    progressBar.style.width = percent + "%";
    prevBtn.disabled = index === 0; 
    nextBtn.textContent = index === total - 1 ? "إرسال" : "التالي";
  }
  function validateCurrentStep() {
    const step = steps[currentIndex];
    const radios = step.querySelectorAll('input[type="radio"]');
    if (radios.length > 0) {
      const names = new Set();
      radios.forEach(r => names.add(r.name));
      for (const name of names) {
        const group = step.querySelectorAll('input[type="radio"][name="' + name + '"]');
        const checked = Array.from(group).some(r => r.checked);
        if (!checked) {
          alert("رجاءً اختر إجابة لهذا السؤال قبل المتابعة.");
          group[0].focus();
          return false;
        }
      }
    }
    const checkboxes = step.querySelectorAll('input[type="checkbox"]');
    if (checkboxes.length > 0) {
      const anyChecked = Array.from(checkboxes).some(c => c.checked);
      if (!anyChecked) {
        alert("رجاءً اختر خياراً واحداً على الأقل قبل المتابعة.");
        checkboxes[0].focus();
        return false;
      }
    }
    const textInputs = step.querySelectorAll(
      'input[type="text"], input[type="number"], input[type="email"], input[type="tel"], textarea'
    );
    for (const input of textInputs) {
      if (input.disabled) continue;
      if (input.value.trim() === "") {
        alert("رجاءً املأ الحقل قبل المتابعة.");
        input.focus();
        return false;
      }
    }
    return true;
  }
  // زر التالي
  nextBtn.addEventListener("click", () => {
    // تأكد أنه جاوب على السؤال الحالي
    if (!validateCurrentStep()) {
      return; // لا تنتقل لو ما جاوب
    }
    if (currentIndex < total - 1) {
      currentIndex++;
      showStep(currentIndex);
    } else {
      //  "إرسال"
      const formData = new FormData(form);
      console.log("Form data:");
      for (const [name, value] of formData.entries()) {
        console.log(name, "=>", value);
      }
      alert("تم إرسال الاستبيان بنجاح، شكرًا لمشاركتك!");
      //  توجيه لصفحة ثانية: 
    }
  });
  // زر السابق
  prevBtn.addEventListener("click", () => {
    if (currentIndex > 0) {
      currentIndex--;
      showStep(currentIndex);
    }
  });  
  showStep(currentIndex);
});