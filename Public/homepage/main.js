document.addEventListener("DOMContentLoaded", () => {
  const moodRange = document.getElementById("moodRange");
  const moodEmoji = document.getElementById("moodEmoji");
  const moodText = document.getElementById("moodText");
  const moodThumb = document.getElementById("moodThumb");
  const moodThumbEmoji = document.getElementById("moodThumbEmoji");
  const moodProgressFill = document.getElementById("moodProgressFill");
  const moodAdvice = document.getElementById("moodAdvice");
  const moodAdviceText = document.getElementById("moodAdviceText");
  const moodIconWrapper = document.getElementById("moodIcon");
  const moodDragHint = document.getElementById("moodDragHint");
  const moodRipple = document.getElementById("moodRipple");

  const choices = document.querySelectorAll(".mood-choice");

  const moods = {
    1: { 
      emoji: "😢", 
      text: "سيّئ جدًا", 
      advice: "نحن هنا من أجلك، خذ نفسًا عميقًا. كل المشاعر مؤقتة وستمر.", 
      color: "#6366F1", // Indigo for deep sadness
      bgGradient: "linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%)"
    },
    2: { 
      emoji: "😞", 
      text: "سيّئ", 
      advice: "من الطبيعي أن تمر بأيام صعبة. امنح نفسك بعض اللطف اليوم.", 
      color: "#8B5CF6", // Purple for sadness
      bgGradient: "linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%)"
    },
    3: { 
      emoji: "😐", 
      text: "عادي", 
      advice: "يوم هادئ، ربما تكون هذه فرصة للاهتمام بنفسك قليلًا.", 
      color: "#9CA3AF", // Gray for neutral
      bgGradient: "linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%)"
    },
    4: { 
      emoji: "😊", 
      text: "جيد", 
      advice: "جميل، استمر في العناية بنفسك والحفاظ على هذا الشعور.", 
      color: "#10B981", // Emerald for happy
      bgGradient: "linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)"
    },
    5: { 
      emoji: "😄", 
      text: "ممتاز", 
      advice: "رائع! طاقتك الإيجابية تستحق أن تستمتع بها وتشاركها مع الآخرين.", 
      color: "#F59E0B", // Amber for very happy
      bgGradient: "linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)"
    }
  };

  let isFirstLoad = true;
  let hasInteracted = false;

  function updateUI(value) {
    const v = Number(value);
    const data = moods[v];
    if (!data) return;

    // Update root CSS Variables for dynamic colors and background
    document.documentElement.style.setProperty('--mood-color', data.color);
    document.documentElement.style.setProperty('--mood-bg-gradient', data.bgGradient);

    // Animate large emoji
    moodEmoji.style.transform = "scale(0.7)";
    setTimeout(() => {
      moodEmoji.textContent = data.emoji;
      moodEmoji.style.transform = "scale(1)";
    }, 150);

    // Update thumb emoji
    if (moodThumbEmoji) {
      moodThumbEmoji.textContent = data.emoji;
    }

    // Update mood title text
    moodText.textContent = data.text;

    // Animate Advice Text
    if (moodAdviceText) {
      if (!isFirstLoad) {
        moodAdviceText.classList.remove("fade-in");
        moodAdviceText.classList.add("fade-out");
        
        if (moodIconWrapper) {
          moodIconWrapper.style.transform = "scale(0.8)";
        }

        setTimeout(() => {
          moodAdviceText.textContent = data.advice;
          moodAdviceText.classList.remove("fade-out");
          moodAdviceText.classList.add("fade-in");
          
          if (moodIconWrapper) {
            moodIconWrapper.style.transform = "scale(1)";
          }
        }, 300);
      } else {
        moodAdviceText.textContent = data.advice;
      }
    }

    // Active state for small emojis
    choices.forEach(el => el.classList.toggle("active", Number(el.dataset.value) === v));

    // Move the thumb and progress fill
    const min = Number(moodRange.min);
    const max = Number(moodRange.max);
    const percent = ((v - min) / (max - min)) * 100;
    
    if (moodThumb) moodThumb.style.right = `calc(${percent}%)`;
    if (moodProgressFill) {
      moodProgressFill.style.width = `calc(${percent}%)`;
      // Soft glow for progress path based on color
      moodProgressFill.style.boxShadow = `0 0 10px ${data.color}80`;
    }

    isFirstLoad = false;
  }

  if (moodRange) {
    moodEmoji.style.transition = "transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
    
    updateUI(moodRange.value);
    
    moodRange.addEventListener("input", (e) => {
      // First interaction logic
      if (!hasInteracted) {
        hasInteracted = true;
        if (moodDragHint) moodDragHint.classList.add("hidden");
        // Stop the constant breathing animation so user feels control
        if (moodThumb) moodThumb.style.animation = "none";
      }

      window.requestAnimationFrame(() => updateUI(e.target.value));
    });

    // Ripple effect on release
    moodRange.addEventListener("change", () => {
      if (moodRipple) {
        moodRipple.classList.remove("active");
        // trigger reflow
        void moodRipple.offsetWidth;
        moodRipple.classList.add("active");
      }
    });

    // Also trigger ripple on touchend / mouseup for better feel
    moodRange.addEventListener("pointerup", () => {
      if (moodRipple) {
        moodRipple.classList.remove("active");
        void moodRipple.offsetWidth;
        moodRipple.classList.add("active");
      }
    });
  }
});


