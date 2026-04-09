const questions = [

{q:"1. يشعر بالحزن وعدم السعادة"},
{q:"2. يشعر باليأس"},
{q:"3. لا يثق بنفسه"},
{q:"4. يقلق كثيرًا"},
{q:"5. لا يستمتع كثيرًا بوقته وأنشطته"},
{q:"6. لا يمكنه الجلوس هادئًا (يتململ كثيرًا)"},
{q:"7. لديه أحلام يقظة أكثر من خطط أو أهداف"},
{q:"8. يتشتت انتباهه بسهولة"},
{q:"9. لديه صعوبة في التركيز"},
{q:"10. كثير النشاط كما لو كان يقوده محرك"},
{q:"11. يتعارك مع الأطفال الآخرين"},
{q:"12. لا يتبع القواعد ولا يهتم لها"},
{q:"13. يُثير أعصاب الآخرين ويُزعجهم"},
{q:"14. يلوم الآخرين على مشاكله"},
{q:"15. يرفض المشاركة"},
{q:"16. يأخذ أشياء ليست له"},
{q:"17. لا يفهم مشاعر الآخرين (قلة التعاطف)"}

];

let currentQuestion = 0;
let totalScore = 0;

const container = document.getElementById("questionContainer");
const progress = document.getElementById("progressBar");

function showQuestion(){

let q = questions[currentQuestion];

let html = `<div class="question"><h3>${q.q}</h3>`;


html += `
<label class="option"><input type="radio" onchange="nextQ(2)">دائمًا</label>
<label class="option"><input type="radio" onchange="nextQ(1)">أحيانًا</label>
<label class="option"><input type="radio" onchange="nextQ(0)">نادرًا</label>
`;

html += `</div>`;

container.innerHTML = html;


progress.style.width = ((currentQuestion+1)/questions.length)*100 + "%";
}

function nextQ(val){

totalScore += val;
currentQuestion++;

if(currentQuestion < questions.length){
showQuestion();
}else{
showResult();
}

}

function showResult(){

let level = "";
let message = "";


if(totalScore <= 11){
  level = "الحالة النفسية مستقرة";
  message = "يبدو أن الطفل يتمتع بحالة نفسية جيدة بشكل عام. يُنصح بالاستمرار في توفير بيئة داعمة ومريحة له 😊";
}
else if(totalScore <= 22){
  level = "مؤشرات تحتاج متابعة";
  message = "هناك بعض المؤشرات التي قد تدل على وجود ضغوط أو صعوبات بسيطة. يُفضل الانتباه لسلوك الطفل والتحدث معه بشكل مستمر، وقد يكون من المفيد استشارة مختص.";
}
else{
  level = "يحتاج إلى دعم نفسي";
  message = "تشير الإجابات إلى وجود صعوبات واضحة قد تؤثر على الحالة النفسية للطفل. يُنصح بحجز جلسة مع أخصائي نفسي للحصول على تقييم ودعم مناسب ❗";
}

container.innerHTML = `

<div class="resultBoxNew">

<h2>نتيجة التقييم</h2>

<div class="miniCard">
<p class="value">${level}</p>
<p class="desc">${message}</p>
</div>

<a href="../index.html" class="btnMain">احجز جلسة</a>

</div>
`;
}
showQuestion();