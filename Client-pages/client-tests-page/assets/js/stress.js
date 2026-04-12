const questions = [
  { q: "1. أعاني كثيرة من الصداع." },
  { q: "2. عندما أجلس للراحة والاسترخاء أجد نفسي منهمكا بأفكار سلبية." },
  { q: "3. يلازمني شعور دائم بعدم الارتياح." },
  { q: "4. نادرا ما أشعر بالاسترخاء التام." },
  { q: "5. أشعر بعدم القدرة على التركيز في ما أقوم به من أعمال" },
  { q: "6. أشعر باستمرار وكأنني أرزح تحت ضغط." },
  { q: "7. أشعر في كثير من الأوقات بالتعب الشديد." },
  { q: "8. كثيرا ما أسرح بأفكار غير مرتبطة في ما أقوم به من أعمال." },
  { q: "9. لا أجد نفسي متحمسا للقيام بالأعمال المختلفة." },
  { q: "10. نادرا ما أشعر بعد النوم بأنني حصلت على قدر كافي من الراحة." },
  { q: "11. كثيرا ما أتشتت بأفكار غير مرغوبة." },
  { q: "12. أشعر عموما أن أعصابي مشدودة دون داع حقيقي لذلك." },
  { q: "13. أشعر في كثير من الأوقات وكأن رأسي سينفجر." },
  { q: "14. أشعر أنني متردد جدا في اتخاذ قراراتي." },
  { q: "15. أشعر أن الأشياء التافهة والصغيرة أصبحت تزعجني." },
  {
    q: "16. غالبا ما أشعر أنني لا أملك الطاقة الكافية للقيام بواجباتي اليومية.",
  },
  { q: "17. كثيرا ما أؤجل ما يجب أن أتخذ به قرارا." },
  { q: "18. أجد أن مشاعري تجرح بسهولة." },
  { q: "19. كثيرا ما أشعر بالارتجاف في أطرافي." },
  { q: "20. كثيرا ما أتجنب اتخاذ قراراتي." },
  {
    q: "21. أشعر أنني أبالغ بردود أفعالي تجاه مشكلات الحياة العادية والبسيطة.",
  },
  { q: "22. كثيرا ما ينتابني تصبب العرق." },
  { q: "23. أشعر أن كثير من أمور حياتي خارجة عن نطاق سيطرتي." },
  { q: "24. تنتابني العصبية لأبسط الأصوات المفاجئة." },
  { q: "25. كثيرا ما أشعر بتزايد في نبضات قلبي." },
  { q: "26. أشعر أنني ضحية للظروف بلا حول ولا قوة." },
  { q: "27. كثيرا ما أعاني من مشاعر القلق بدون سبب ظاهر" },
  { q: "28. كثيرا ما يصيبني الأرق." },
  { q: "29. كثيرا ما أعاني من نوبات الخوف." },
  { q: "30. كثيرا ما ينتابني الكوابيس." },
  { q: "31. أتوقع أسوأ العواقب لأية مخاطر مهما كانت بسيطة." },
  { q: "32. كثيرا ما أعاني من النوم المتقطع." },
  { q: "33. أحس بمسؤولية شخصية تجاه حدوث أي شيء خاطئ." },
  { q: "34. غالبا ما أكون منهك القوى." },
  { q: "35. أصنع من الحبة قبة." },
];

let currentQuestion = 0;
let totalScore = 0;

const container = document.getElementById("questionContainer");
const progress = document.getElementById("progressBar");

function showQuestion() {
  let q = questions[currentQuestion];

  let html = `<div class="question"><h3>${q.q}</h3>`;

  html += `
<label class="option"><input type="radio" onchange="nextQ(2)">تنطبق كثيراً</label>
<label class="option"><input type="radio" onchange="nextQ(1)">تنطبق أحياناً</label>
<label class="option"><input type="radio" onchange="nextQ(0)">لا تنطبق</label>
`;

  html += `</div>`;

  container.innerHTML = html;

  progress.style.width = ((currentQuestion + 1) / questions.length) * 100 + "%";
}

function nextQ(val) {
  totalScore += val;
  currentQuestion++;

  if (currentQuestion < questions.length) {
    showQuestion();
  } else {
    showResult();
  }
}

function showResult() {
  let level = "";

  if (totalScore <= 23) level = "توتر منخفض";
  else if (totalScore <= 46) level = "توتر متوسط";
  else level = "توتر مرتفع";

  container.innerHTML = `

<div class="resultBoxNew">

<h2>تحليل مستوى التوتر</h2>

<div class="miniCard">
<p class="value">${level}</p>
<p class="desc">
${
  level === "توتر منخفض"
    ? "وضعك جيد"
    : level === "توتر متوسط"
      ? "لديك بعض الضغوط"
      : "يوجد ضغط مرتفع ويُفضل مراجعة أخصائي"
}
</p>
</div>

<a href="../client-dashboard-page/index.html" class="btnMain">احجز جلسة</a>

</div>
`;
}

showQuestion();
