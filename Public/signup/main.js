document.addEventListener("DOMContentLoaded", function () {
  const steps = document.querySelectorAll(".form-step");
  const currentStepEl = document.getElementById("currentStep");
  const totalStepsEl = document.getElementById("totalSteps");
  const progressBar = document.getElementById("progressBar");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const form = document.getElementById("surveyForm");

  if (!steps.length || !currentStepEl || !totalStepsEl || !progressBar || !prevBtn || !nextBtn || !form) {
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

  // التحقق من الخطوة الظاهرة حالياً فقط.
  // نتجاهل أي حقل معطّل (disabled) لأنه يمثّل سؤالاً شرطياً مخفياً (مثل موافقة الوالدين
  // أو تفاصيل المشكلة الجسدية عند اختيار "لا")، فلا يجب أن يُطلب من المستخدم تعبئته.
  function validateCurrentStep() {
    const step = steps[currentIndex];

    // 1) أسئلة الاختيار الواحد (radio): الإجباري فقط المجموعات المفعّلة (غير المعطّلة)
    const radioNames = new Set();
    step
      .querySelectorAll('input[type="radio"]:not([disabled])')
      .forEach((r) => radioNames.add(r.name));

    for (const name of radioNames) {
      const group = step.querySelectorAll(
        'input[type="radio"][name="' + name + '"]:not([disabled])'
      );
      if (group.length === 0) continue;
      const checked = Array.from(group).some((r) => r.checked);
      if (!checked) {
        showWarningAlert(
          name === "parental_consent"
            ? "يرجى تحديد ما إذا كان هناك موافقة من أحد الوالدين."
            : "رجاءً اختر إجابة لهذا السؤال قبل المتابعة."
        );
        group[0].focus();
        return false;
      }
    }

    // 2) أسئلة الاختيار المتعدد (checkbox): يجب اختيار خيار واحد على الأقل
    const checkboxes = step.querySelectorAll('input[type="checkbox"]');
    if (checkboxes.length > 0) {
      const anyChecked = Array.from(checkboxes).some((c) => c.checked);
      if (!anyChecked) {
        showWarningAlert("رجاءً اختر خياراً واحداً على الأقل قبل المتابعة.");
        checkboxes[0].focus();
        return false;
      }
    }

    // 3) الحقول النصية: نتحقق فقط من الحقول المفعّلة التي تحمل خاصية required
    const textInputs = step.querySelectorAll(
      'input[type="text"]:not([disabled]), input[type="number"]:not([disabled]), input[type="email"]:not([disabled]), input[type="tel"]:not([disabled]), textarea:not([disabled])'
    );
    for (const input of textInputs) {
      if (input.hasAttribute("required") && input.value.trim() === "") {
        showErrorAlert("رجاءً املأ هذا الحقل قبل المتابعة.");
        input.focus();
        return false;
      }
    }

    // 4) قاعدة العمر وموافقة الوالدين (تُطبَّق فقط على الخطوة التي تحتوي حقل العمر)
    if (!validateAgeAndConsent(step)) {
      return false;
    }

    return true;
  }

  // ─── منطق العمر وموافقة الوالدين ───
  // مرتبط حصراً بخطوة العمر: لا يعمل عند الانتقال بين باقي الأسئلة.
  const parentalConsentGroup = document.getElementById("parentalConsentGroup");

  function setParentalConsentVisible(visible) {
    if (!parentalConsentGroup) return;
    const radios = parentalConsentGroup.querySelectorAll('input[name="parental_consent"]');
    if (visible) {
      parentalConsentGroup.style.display = "";
      radios.forEach((r) => (r.disabled = false));
    } else {
      parentalConsentGroup.style.display = "none";
      // إخفاء = تعطيل + إلغاء الاختيار حتى يُستثنى تماماً من التحقق ولا تبقى حالة قديمة
      radios.forEach((r) => {
        r.disabled = true;
        r.checked = false;
      });
    }
  }

  function validateAgeAndConsent(step) {
    const ageInput = step.querySelector('input[name="age"]');
    if (!ageInput) {
      return true; // لسنا في خطوة العمر → لا علاقة لهذا المنطق بباقي الأسئلة
    }

    const age = parseInt(ageInput.value.trim(), 10);

    // عمر صالح ≥ 18 → لا حاجة لموافقة، نُخفي السؤال الشرطي ونتابع
    if (Number.isNaN(age) || age >= 18) {
      setParentalConsentVisible(false);
      return true;
    }

    // عمر أقل من 18:
    const isHidden = !parentalConsentGroup || parentalConsentGroup.style.display === "none";
    if (isHidden) {
      // أول مرة نكتشف فيها العمر < 18 → نُظهر سؤال الموافقة وننبّه مرة واحدة فقط
      setParentalConsentVisible(true);
      showWarningAlert("لا يمكن إنشاء حساب لمن هم أقل من 18 سنة إلا بوجود وموافقة أحد الوالدين.");
      return false;
    }

    // السؤال ظاهر؛ تم اختيار الإجابة مسبقاً (التحقق في الخطوة 1 يضمن وجود اختيار)
    const answer = form.querySelector('input[name="parental_consent"]:checked');
    if (answer && answer.value === "NO") {
      showErrorAlert("لا يمكن إكمال التسجيل لمن هم أقل من 18 سنة دون موافقة وحضور أحد الوالدين.");
      return false;
    }

    // موافقة = نعم → نتابع دون إعادة إظهار التنبيه
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

      // Toast نجاح ثم الانتقال لإكمال بيانات الحساب
      showSuccessToast("تم حفظ إجاباتك، أكمل بيانات حسابك الآن");
      setTimeout(function () {
        window.location.href = "../sendpage/index.php";
      }, 900);
    }
  });

  prevBtn.addEventListener("click", () => {
    if (currentIndex > 0) {
      currentIndex--;
      showStep(currentIndex);
    }
  });

  // ─── منطق تفاصيل المشكلة الجسدية ───
  // "نعم" → الحقل مفعّل وإلزامي (required).
  // "لا" أو لا اختيار → الحقل غير إلزامي، مُفرّغ، ومعطّل (فيُستثنى من التحقق ولا تبقى رسالة خطأ).
  const physicalIssuesRadios = form.querySelectorAll('input[name="physical_issues"]');
  const physicalDetailsInput = form.querySelector('input[name="physical_details"]');

  function updatePhysicalDetailsState() {
    if (!physicalDetailsInput) return;
    const selected = form.querySelector('input[name="physical_issues"]:checked');
    if (selected && selected.value === "YES") {
      physicalDetailsInput.disabled = false;
      physicalDetailsInput.required = true;
    } else {
      // عند اختيار "لا" نزيل الإلزام ونُفرّغ الحقل ونعطّله حتى لا تبقى أي حالة خطأ سابقة
      physicalDetailsInput.required = false;
      physicalDetailsInput.disabled = true;
      physicalDetailsInput.value = "";
    }
  }

  physicalIssuesRadios.forEach((radio) => {
    radio.addEventListener("change", updatePhysicalDetailsState);
  });
  updatePhysicalDetailsState();

  // عند تعديل العمر إلى 18 أو أكثر نُخفي سؤال موافقة الوالدين فوراً (تنظيف الحالة)
  const ageInputField = document.getElementById("ageInput");
  if (ageInputField) {
    ageInputField.addEventListener("input", function () {
      const age = parseInt(ageInputField.value.trim(), 10);
      if (Number.isNaN(age) || age >= 18) {
        setParentalConsentVisible(false);
      }
    });
  }

  showStep(currentIndex);
});
