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
    console.error("بعض عناصر النموذج غير موجودة في الـ HTML.");
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

    const checkboxes = step.querySelectorAll('input[type="checkbox"]');
    if (checkboxes.length > 0) {
      const anyChecked = Array.from(checkboxes).some((c) => c.checked);
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
      const name = input.name;
      const value = input.value.trim();

      if (name === "physical_details") {
        const physicalIssues = form.querySelector(
          'input[name="physical_issues"]:checked'
        );
        if (!physicalIssues || physicalIssues.value !== "YES") {
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

  nextBtn.addEventListener("click", () => {
    if (!validateCurrentStep()) return;

    if (currentIndex < total - 1) {
      currentIndex++;
      showStep(currentIndex);
    } else {
      const formData = new FormData(form);
      const surveyData = {};

      for (const [name, value] of formData.entries()) {
        if (name.endsWith("[]")) {
          const key = name.slice(0, -2);
          if (!surveyData[key]) {
            surveyData[key] = [];
          }
          surveyData[key].push(value);
        } else {
          surveyData[name] = value;
        }
      }

      if (Array.isArray(surveyData.symptoms)) {
        surveyData.symptoms = surveyData.symptoms.join("،");
      }
      if (Array.isArray(surveyData.repeated_symptoms)) {
        surveyData.repeated_symptoms = surveyData.repeated_symptoms.join("،");
      }

      sessionStorage.setItem("surveyData", JSON.stringify(surveyData));

      window.location.href = "../sendpage/index.php";
    }
  });

  prevBtn.addEventListener("click", () => {
    if (currentIndex > 0) {
      currentIndex--;
      showStep(currentIndex);
    }
  });

  const physicalIssuesRadios = form.querySelectorAll(
    'input[name="physical_issues"]'
  );
  const physicalDetailsInput = form.querySelector(
    'input[name="physical_details"]'
  );

  function updatePhysicalDetailsState() {
    if (!physicalDetailsInput) return;
    const selected = form.querySelector('input[name="physical_issues"]:checked');
    if (selected && selected.value === "NO") {
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

  showStep(currentIndex);
});
