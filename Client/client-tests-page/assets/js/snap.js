const questions = [

/* 1 - 9 (عدم الانتباه IN) */

{
question:"1. غالباً لا ينتبه للتفاصيل الدقيقة ولا يبالي للاخطاء اثناء قيامة بواجباته المدرسية.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"2. غالباً يظهر صعوبة في استمرارية الانتباه في أداء المهمات أو اللعب.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"3. غالباً لا يبدي اهتمامأ للاصغاء عندما يتم التحدث إليه مباشرة.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"4. غالباً لا يتبع التعليمات ولا ينهي مهماته وواجباته المدرسية.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"5. غالباً لديه صعوبة في تنظيم المهمات والنشاطات .",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"6. غالباً يتجنب المشاركة في المهمات التي تتطلب جهد عقلي مستمر (مثال: المهمات المدرسية او البيتية).",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"7. يفقد الأشياء عند أداء المهمات او النشاطات، مثل: الواجبات المدرسية، الاقلام، الكتب، الادوات، الالعاب).",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"8. غالباً يتشتت بسبب مثيرات خارجية.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"9. غالباً كثير النسيان لنشاطات الحياة اليومية.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},

/* 10 - 18 (الحركة الزائدة H/IM) */

{
question:"10. غالباً يتحرك بشكل مستمر مستخدما يديه ورجليه حتى أثناء جلوسه على مقعده.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"11. غالباً يغادر مقعده في الحصة أو في حالات أخرى بحيث يتوقع منه البقاء جالسا.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"12. غالباً يركض أو يتسلق بكثرة في مواقف غير ملائمة.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"13. غالباً يواجه صعوبة في اللعب في أوقات الفراغ بشكل هادئ.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"14. غالبا يتصرف حسب ما يطلب منه الآخرين.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"15. غالباً يتكلم كثيرا.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"16. غالباً يعطي أجابته قبل الانتهاء من السؤال المطروح عليه.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"17. غالباً يواجه صعوبة في انتظار دوره.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"18. غالباً يقاطع الأخرين أثناء اللعب أو التحدث.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},

/* 19 - 26 (ODD) */

{
question:"19. غالباً متقلب المزاج.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"20. غالباً يتجادل كثيرا مع الراشدين.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"21. غالباً يرفض طلبات أو قوانين من يكبره في السن.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"22. يتعمد إزعاج الأخرين.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"23. غالباً يلقي اللوم على الأخرين على أخطائه أو سوء تصرفه.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"24. غالباً سهل استفزازه و مضايقته من حوله.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"25. غالباً سريع الغضب و الغيظ.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
},
{
question:"26. غالباً يبدي نزعة الحقد و الانتقاد.",
answers:[
{text:"لا يحدث",score:0},
{text:"أحياناً",score:1},
{text:"غالباً",score:2},
{text:"دائماً",score:3}
]
}

];

let currentQuestion=0;

let inScore=0;
let hyperScore=0;
let oddScore=0;

const container=document.getElementById("questionContainer");
const progress=document.getElementById("progressBar");

function showQuestion(){

let q=questions[currentQuestion];

let html=`<div class="question"><h3>${q.question}</h3>`;

q.answers.forEach(a=>{
html+=`
<label class="option">
<input type="radio" name="answer" onchange="nextQuestion(${a.score})">
${a.text}
</label>`;
});

html+=`</div>`;

container.innerHTML=html;

progress.style.width=((currentQuestion+1)/questions.length)*100+"%";
}

function nextQuestion(value){

if(currentQuestion<=8) inScore+=value;
else if(currentQuestion<=17) hyperScore+=value;
else oddScore+=value;

currentQuestion++;

if(currentQuestion<questions.length){
showQuestion();
}else{
showResult();
}

}

function getLevel(score,type){

if(type==="IN"){
if(score<13) return "لا توجد مشكلة واضحة";
if(score<=17) return "أعراض بسيطة";
if(score<=22) return "أعراض متوسطة";
return "أعراض شديدة";
}

if(type==="H"){
if(score<13) return "لا توجد مشكلة واضحة";
if(score<=17) return "أعراض بسيطة";
if(score<=22) return "أعراض متوسطة";
return "أعراض شديدة";
}

if(type==="ODD"){
if(score<8) return "لا توجد مشكلة واضحة";
if(score<=13) return "أعراض بسيطة";
if(score<=18) return "أعراض متوسطة";
return "أعراض شديدة";
}

}

function showResult(){

container.innerHTML=`

<div class="resultBox">

<h2>نتيجة التقييم</h2>

<p><strong>عدم الانتباه:</strong> ${getLevel(inScore,"IN")}</p>

<p><strong>فرط الحركة:</strong> ${getLevel(hyperScore,"H")}</p>

<p><strong>التحدي الاعتراضي:</strong> ${getLevel(oddScore,"ODD")}</p>

<br>

<a href="../client-dashboard-page/index.php" class="bookBtn">
احجز جلسة مع أخصائي
</a>

</div>
`;

}

showQuestion();