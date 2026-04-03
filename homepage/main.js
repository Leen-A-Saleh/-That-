document.addEventListener("DOMContentLoaded", () => {
  const moodRange = document.getElementById("moodRange");
  const moodEmoji = document.getElementById("moodEmoji");
  const moodText = document.getElementById("moodText");
  const moodThumb = document.getElementById("moodThumb");
  const moodAdvice = document.getElementById("moodAdvice");

  const choices = document.querySelectorAll(".mood-choice");

  const moods = {
    1: { emoji: "😢", text: "سيّئ جدًا", advice: "نقترح تمارين تهدئة سريعة مع تنفّس عميق لمدة 10 دقائق." },
    2: { emoji: "😕", text: "سيّئ", advice: "نقترح جلسة تخفيف توتر قصيرة + تمرين تنظيم الأفكار." },
    3: { emoji: "😶", text: "عادي", advice: "نقترح تأمل بسيط وتمرين تركيز لتحسين يومك." },
    4: { emoji: "😊", text: "جيد", advice: "نقترح تعزيز الإيجابية + تمارين مرونة نفسية بسيطة." },
    5: { emoji: "🤩", text: "ممتاز", advice: "نقترح جلسة الحفاظ على المزاج + روتين قصير يومي." }
  };

  function updateUI(value) {
    const v = Number(value);
    const data = moods[v];
    if (!data) return;

    // emoji + text + advice
    moodEmoji.textContent = data.emoji;
    moodText.textContent = data.text;
    moodAdvice.querySelector(".mood-advice-text").textContent = data.advice;

    // active choice
    choices.forEach(el => el.classList.toggle("active", Number(el.dataset.value) === v));

    // thumb position: 1..5
    const min = Number(moodRange.min);
    const max = Number(moodRange.max);
    const percent = ((v - min) / (max - min)) * 100; // 0..100
    moodThumb.style.left = `calc(${percent}% )`;
  }

  if (moodRange) {
    updateUI(moodRange.value);
    moodRange.addEventListener("input", (e) => updateUI(e.target.value));
  }
});


