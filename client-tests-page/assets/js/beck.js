const questions = [
{
question:"1. الحزن",
answers:[
{text:"أنا لا أشعر بالحزن",score:0},
{text:"أنا أشعر بالحزن أحياناً",score:1},
{text:"أنا حزين طيلة الوقت ولا أستطيع التخلص من هذا الشعور",score:2},
{text:"أنا حزين جداً أو غير سعيد إلى حد لا أستطيع تحمله",score:3}
]
},
{
question:"2. نظرتي للمستقبل",
answers:[
{text:"لست متشائماً في نظرتي للمستقبل",score:0},
{text:"أشعر بأن المستقبل غير مشجع",score:1},
{text:"أشعر بأنه لم يعد لدي شيء أتطلع إليه",score:2},
{text:"أشعر بأن المستقبل لا أمل فيه",score:3}
]
},
{
question:"3. الشعور بالفشل",
answers:[
{text:"لا أشعر بأنني شخص فاشل",score:0},
{text:"أشعر بأنني فشلت أكثر من الإنسان العادي",score:1},
{text:"عندما أنظر إلى حياتي الماضية أرى الكثير من الفشل",score:2},
{text:"أشعر بأنني شخص فاشل تماماً",score:3}
]
},
{
question:"4. الرضا عن الحياة",
answers:[
{text:"أشعر بالرضا تجاه ما أفعله في حياتي",score:0},
{text:"لم أعد أستمتع بالأشياء كما كنت سابقاً",score:1},
{text:"لم أعد أشعر بالرضا الحقيقي عن أي شيء",score:2},
{text:"أنا غير راضٍ وأشعر بالملل من كل شيء",score:3}
]
},
{
question:"5. الشعور بالذنب",
answers:[
{text:"لا أشعر بالذنب",score:0},
{text:"أشعر بالذنب في كثير من الأوقات",score:1},
{text:"أشعر بالذنب معظم الوقت",score:2},
{text:"أشعر بالذنب طيلة الوقت",score:3}
]
},
{
question:"6. الشعور بالعقاب",
answers:[
{text:"لا أشعر بأنني أعاقب",score:0},
{text:"أشعر أنني قد أعاقب",score:1},
{text:"أتوقع أن أعاقب",score:2},
{text:"أشعر بأنني أعاقب فعلاً",score:3}
]
},
{
question:"7. خيبة الأمل في النفس",
answers:[
{text:"لا أشعر بخيبة أمل في نفسي",score:0},
{text:"أشعر بخيبة أمل في نفسي",score:1},
{text:"أنا مشمئز من نفسي",score:2},
{text:"أنا أكره نفسي",score:3}
]
},
{
question:"8. لوم النفس",
answers:[
{text:"لا أشعر بأنني أسوأ من الآخرين",score:0},
{text:"أنتقد نفسي بسبب أخطائي",score:1},
{text:"ألوم نفسي طيلة الوقت على أخطائي",score:2},
{text:"ألوم نفسي على أي شيء سيء يحدث",score:3}
]
},
{
question:"9. أفكار الانتحار",
answers:[
{text:"ليست لدي أفكار لقتل نفسي",score:0},
{text:"لدي أفكار لكن لن أنفذها",score:1},
{text:"أرغب في قتل نفسي",score:2},
{text:"سأقتل نفسي إذا أتيحت لي الفرصة",score:3}
]
},
{
question:"10. البكاء",
answers:[
{text:"لا أبكي أكثر من المعتاد",score:0},
{text:"أبكي أكثر من المعتاد",score:1},
{text:"أبكي طوال الوقت",score:2},
{text:"لا أستطيع البكاء رغم رغبتي",score:3}
]
},
{
question:"11. التوتر",
answers:[
{text:"لست أكثر توتراً من المعتاد",score:0},
{text:"أتوتر بسهولة أكثر من المعتاد",score:1},
{text:"أشعر بالتوتر طيلة الوقت",score:2},
{text:"لم أعد أنفعل بأي شيء",score:3}
]
},
{
question:"12. الاهتمام بالناس",
answers:[
{text:"لم أفقد اهتمامي بالناس",score:0},
{text:"اهتمامي بالناس أقل من السابق",score:1},
{text:"فقدت معظم اهتمامي بالناس",score:2},
{text:"فقدت كل اهتمامي بالناس",score:3}
]
},
{
question:"13. اتخاذ القرار",
answers:[
{text:"قدرتي على اتخاذ القرار لم تتغير",score:0},
{text:"أؤجل اتخاذ القرار أكثر من قبل",score:1},
{text:"لدي صعوبة في اتخاذ القرار",score:2},
{text:"لا أستطيع اتخاذ قرارات",score:3}
]
},
{
question:"14. المظهر",
answers:[
{text:"لا أشعر أن مظهري أسوأ",score:0},
{text:"قلق من أنني أبدو أكبر سناً",score:1},
{text:"هناك تغيرات تجعلني أقل جاذبية",score:2},
{text:"أعتقد أنني أبدو بشعاً",score:3}
]
},
{
question:"15. العمل",
answers:[
{text:"أستطيع العمل كما من قبل",score:0},
{text:"أحتاج جهداً إضافياً للبدء",score:1},
{text:"أضغط نفسي لأعمل",score:2},
{text:"لا أستطيع القيام بأي عمل",score:3}
]
},
{
question:"16. النوم",
answers:[
{text:"أنام كالمعتاد",score:0},
{text:"نومي لم يعد كالمعتاد",score:1},
{text:"أستيقظ مبكراً ولا أستطيع النوم",score:2},
{text:"أستيقظ قبل ساعات ولا أعود للنوم",score:3}
]
},
{
question:"17. الإرهاق",
answers:[
{text:"لا أشعر بإرهاق أكثر من المعتاد",score:0},
{text:"أتعب بسرعة",score:1},
{text:"أشعر بالإرهاق من أي شيء",score:2},
{text:"متعب جداً للقيام بأي عمل",score:3}
]
},
{
question:"18. الشهية",
answers:[
{text:"شهيتي طبيعية",score:0},
{text:"شهيتي أقل",score:1},
{text:"شهيتي أسوأ بكثير",score:2},
{text:"لا شهية لدي إطلاقاً",score:3}
]
},
{
question:"19. الوزن",
answers:[
{text:"لم أفقد وزني",score:0},
{text:"فقدت أكثر من 2 كغ",score:1},
{text:"فقدت أكثر من 4.5 كغ",score:2},
{text:"فقدت أكثر من 6.5 كغ",score:3}
]
},
{
question:"20. القلق على الصحة",
answers:[
{text:"لا أقلق على صحتي",score:0},
{text:"أقلق بسبب أعراض جسدية",score:1},
{text:"قلق جداً بشأن صحتي",score:2},
{text:"قلق لدرجة لا أفكر بشيء آخر",score:3}
]
}
];

let currentQuestion=0;
let score=0;

const container=document.getElementById("questionContainer");
const progress=document.getElementById("progressBar");

function showQuestion(){

let q=questions[currentQuestion];

let html=`<div class="question"><h3>${q.question}</h3>`;

q.answers.forEach(a=>{
html+=`
<label class="option">
<input type="radio" name="answer" value="${a.score}" onchange="nextQuestion(${a.score})">
${a.text}
</label>
`;
});

html+=`</div>`;

container.innerHTML=html;

progress.style.width=((currentQuestion+1)/questions.length)*100+"%";
}

function nextQuestion(value){

score+=value;

currentQuestion++;

if(currentQuestion<questions.length){

setTimeout(showQuestion,300);

}else{

showResult();

}

}

function showResult(){

let title="";
let desc="";
let color="";
let percent=(score/60)*100; // 60 أعلى مجموع

if(score>=10 && score<=15){
title="اكتئاب بسيط";
desc="بعض الأعراض الخفيفة للاكتئاب قد تكون موجودة.";
color="#e9c46a";
}
else if(score>=16 && score<=19){
title="اكتئاب متوسط";
desc="هناك مؤشرات على اكتئاب متوسط ويُفضل الانتباه للحالة النفسية.";
color="#f4a261";
}
else if(score>=20 && score<=29){
title="اكتئاب متوسط إلى شديد";
desc="تظهر لديك أعراض اكتئاب ملحوظة.";
color="#e76f51";
}
else if(score>=30){
title="اكتئاب شديد";
desc="توجد أعراض اكتئاب قوية وينصح بالتواصل مع مختص نفسي.";
color="#e63946";
}
else{
title="لا يوجد اكتئاب";
desc="لا توجد مؤشرات واضحة على الاكتئاب.";
color="#2a9d8f";
}

container.innerHTML=`

<div class="resultBox">

<h2>نتيجتك في اختبار الاكتئاب</h2>

<div class="result-scale">

<div class="scale-indicator" style="left:${percent}%"></div>

</div>

<h3 style="color:${color};font-size:24px;margin-bottom:10px;">
${title}
</h3>

<p class="resultDesc">
${desc}
<br><br>
هذا الاختبار أداة مساعدة فقط ولا يغني عن استشارة مختص نفسي.
</p>

<a href="../index.html" class="bookBtn">
احجز جلسة مع أخصائي
</a>

</div>

`;

}
showQuestion();