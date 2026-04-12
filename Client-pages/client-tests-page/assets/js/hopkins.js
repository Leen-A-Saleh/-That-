const questions = [
  {
    question: "1. خوف مفاجئ وبدون سبب",
    answers: [
      { text: "أبداً ", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "2. الشعور بالخوف",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "3. الإغماء، الدوار أو الضعف",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "4. الشعور بالغضب (العصبية)",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "5. خفقان في القلب (سرعة وزيادة ضربات القلب)",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "6. الارتعاش (الرعشة)",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "7. الشعور بالتشنج والوهن (الخمول)",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "8. صداع",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "9. نوبات رعب (الفزع)",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "10. شعور بعدم الارتياح وعدم الثبات أوالاستقرار بمكان ما",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },

  /* 11 - 25 */

  {
    question: "11. الشعور بانعدام الطاقة والهبوط",
    answers: [
      { text: "أبداً لا", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "12. معاقبة (محاسبة) الذات حول كثير من الامور",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "13. سهولة البكاء",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "14. قلة الرغبة في ممارسة الجنس والشعور باللذة والمتعة الجنسية",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "15. قلة الشهية",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "16. صعوبة في النوم أو البقاء نائما",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "17. الشعور باليأس حول المستقبل",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "18. الشعور بالضيق والانقباض ",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "19. الشعور بالوحدة",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "20. التفكير بالانتحار",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "21.  الشعور بالوقوع في فخ ما أو القاء القبض عليه",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "22.  كثرة القلق حول أشياء كثيرة",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "23. انعدام الرغبة في أي شيء",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question:
      "24. الشعور بصعوبة القيام بأي عمل (أو ان القيام بأي عمل يتطلب جهدا كبيرا)",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
  {
    question: "25. الشعور بعدم الاهمية(الشعور برخص الذات)",
    answers: [
      { text: "أبداً", score: 1 },
      { text: "قليلاً", score: 2 },
      { text: "إلى حد كبير", score: 3 },
      { text: "بشدة", score: 4 },
    ],
  },
];

let currentQuestion = 0;
let anxietyScore = 0;
let depressionScore = 0;
let totalScore = 0;

const container = document.getElementById("questionContainer");
const progress = document.getElementById("progressBar");

const sectionTitle = document.getElementById("sectionTitle");

function showQuestion() {
  let q = questions[currentQuestion];
  if (currentQuestion <= 9) {
    sectionTitle.innerText = "الجزء الأول: أعراض القلق";
  } else {
    sectionTitle.innerText = "الجزء الثاني: أعراض الاكتئاب";
  }

  let html = `<div class="question"><h3>${q.question}</h3>`;

  q.answers.forEach((a) => {
    html += `
<label class="option">
<input type="radio" name="answer" onchange="nextQuestion(${a.score})">
${a.text}
</label>`;
  });

  html += `</div>`;

  container.innerHTML = html;
  progress.style.width = ((currentQuestion + 1) / questions.length) * 100 + "%";
}

function nextQuestion(value) {
  totalScore += value;

  if (currentQuestion <= 9) {
    anxietyScore += value;
  } else {
    depressionScore += value;
  }

  currentQuestion++;

  if (currentQuestion < questions.length) {
    showQuestion();
  } else {
    showResult();
  }
}

function showResult() {
  let anxietyAvg = anxietyScore / 10;
  let depressionAvg = depressionScore / 15;
  let totalAvg = totalScore / 25;

  function getLevel(val) {
    if (val < 1.75)
      return { level: "طبيعي", color: "#22c55e", desc: "لا توجد أعراض ملحوظة" };
    if (val < 2.5)
      return {
        level: "متوسط",
        color: "#facc15",
        desc: "توجد بعض الأعراض التي تحتاج انتباه",
      };
    return {
      level: "مرتفع",
      color: "#ef4444",
      desc: "يوجد أعراض واضحة ويُفضل مراجعة أخصائي",
    };
  }

  let anxiety = getLevel(anxietyAvg);
  let depression = getLevel(depressionAvg);

  container.innerHTML = `

<div class="resultBoxNew">

<h2>تحليل حالتك النفسية</h2>

<div class="miniCard">
<h3>القلق</h3>
<p class="value" style="color:${anxiety.color}">
${anxiety.level}
</p>
<p class="desc">${anxiety.desc}</p>
</div>

<div class="miniCard">
<h3>الاكتئاب</h3>
<p class="value" style="color:${depression.color}">
${depression.level}
</p>
<p class="desc">${depression.desc}</p>
</div>

<div class="finalAdvice">
${
  totalAvg < 1.75
    ? " وضعك جيد، استمر بالاهتمام بنفسك."
    : " ننصحك بحجز جلسة مع أخصائي للحصول على دعم أفضل."
}
</div>

<a href="../client-dashboard-page/index.html" class="btnMain">
احجز جلسة
</a>

</div>
`;
}

showQuestion();
