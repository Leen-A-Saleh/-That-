let config = {
  type: Phaser.AUTO,
  scale: {
    mode: Phaser.Scale.FIT,
    autoCenter: Phaser.Scale.CENTER_BOTH,
    width: 1000,
    height: 650,
  },
  backgroundColor: "#ffffff",
  parent: "game-container",
  scene: { preload, create },
};

let game = new Phaser.Game(config);

let score = 0;
let questionIndex = 0;
let selectedQuestions = [];
let currentLevel = 1;

let currentContainer;

let questionsLevel1 = [
  {
    question: "ما لون البطانية على السرير؟",
    answers: ["أحمر", "أزرق", "أخضر"],
    correct: 0,
    sound: "q1",
  },
  {
    question: "كم جهاز تحكم موجود؟",
    answers: ["1", "2", "3"],
    correct: 1,
    sound: "q2",
  },
  {
    question: "أين توجد الكرة؟",
    answers: ["على السرير", "على الأرض", "على المكتب"],
    correct: 1,
    sound: "q3",
  },
  {
    question: "ما لون السجادة الدائرية؟",
    answers: ["زرقاء", "حمراء", "صفراء"],
    correct: 0,
    sound: "q4",
  },
  {
    question: "ما لون الستائر؟",
    answers: ["أحمر", "أخضر", "أزرق"],
    correct: 0,
    sound: "q5",
  },
];

let questionsLevel2 = [
  {
    question: "كم كرة موجودة؟",
    answers: ["1", "2", "3"],
    correct: 1,
    sound: "q6",
  },
  {
    question: "ما لَونُ الزُّحليقة؟",
    answers: ["أحمر", "أزرق", "أصفر"],
    correct: 0,
    sound: "q7",
  },
  {
    question: "كم فراشة في السماء؟",
    answers: ["1", "2", "3"],
    correct: 1,
    sound: "q8",
  },
  {
    question: "ماذا يوجد فوق الشجرة؟",
    answers: ["بيت عصفور", "كرة", "فراشة"],
    correct: 0,
    sound: "q9",
  },
  {
    question: "كم غيمة في السماء؟",
    answers: ["1", "3", "4"],
    correct: 2,
    sound: "q10",
  },
];

function preload() {
  this.load.image("room", "assets/room.jpg");
  this.load.image("garden", "assets/garden.png");

  this.load.audio("q1", "./assets/q1.mp3");
  this.load.audio("q2", "./assets/q2.mp3");
  this.load.audio("q3", "./assets/q3.mp3");
  this.load.audio("q4", "./assets/q4.mp3");
  this.load.audio("q5", "./assets/q5.mp3");
  this.load.audio("q6", "./assets/q2.1.mp3");
  this.load.audio("q7", "./assets/q2.2.mp3");
  this.load.audio("q8", "./assets/q2.3.mp3");
  this.load.audio("q9", "./assets/q2.4.mp3");
  this.load.audio("q10", "./assets/q2.5.mp3");

  this.load.audio("correct", "./assets/correct.mp3");
  this.load.audio("wrong", "./assets/wrong.mp3");
  this.load.audio("clap", "./assets/clap.mp3");
  this.load.audio("guide", "./assets/guide.mp3");
  this.load.audio("tick", "./assets/tick.mp3");
}

function create() {
  score = 0;
  questionIndex = 0;
  selectedQuestions = questionsLevel1;
  showImage.call(this);
}

function newScreen(scene) {
  if (currentContainer) {
    currentContainer.destroy();
  }
  currentContainer = scene.add.container();
}

function showImage() {
  let scene = this;
  newScreen(scene);

  let title = scene.add
    .text(500, 60, "تأمل الصورة جيداً", {
      fontSize: "25px",
      color: "#4fa79d",
    })
    .setOrigin(0.5);

  let progressBg = scene.add.rectangle(500, 120, 400, 20, 0xdddddd);
  let progressBar = scene.add
    .rectangle(300, 120, 400, 20, 0x4fa79d)
    .setOrigin(0, 0.5);

  let imageKey = currentLevel === 1 ? "room" : "garden";
  let image = scene.add.image(500, 380, imageKey).setDisplaySize(820, 470);

  currentContainer.add([title, progressBg, progressBar, image]);

  let timeLeft = 6;

  let guideSound = scene.sound.add("guide");
  guideSound.play();

  let timerSound = scene.sound.add("tick");
  timerSound.play({ loop: true });

  scene.time.addEvent({
    delay: 1000,
    repeat: 5,
    callback: function () {
      timeLeft--;
      progressBar.width -= 400 / 6;

      if (timeLeft === 0) {
        scene.tweens.add({
          targets: image,
          alpha: 0,
          duration: 600,
          onComplete: function () {
            guideSound.stop();
            timerSound.stop();

            showQuestion.call(scene);
          },
        });
      }
    },
  });
}

function showQuestion() {
  let scene = this;

  if (questionIndex >= selectedQuestions.length) {
    showResult.call(scene);
    return;
  }

  newScreen(scene);

  let q = selectedQuestions[questionIndex];

  let box = scene.add
    .rectangle(500, 325, 800, 450, 0xffffff)
    .setStrokeStyle(4, 0x4fa79d);

  let title = scene.add
    .text(
      500,
      150,
      "سؤال " + (questionIndex + 1) + " من " + selectedQuestions.length,
      { fontSize: "28px", color: "#4fa79d" },
    )
    .setOrigin(0.5);

  let questionText = scene.add
    .text(500, 220, q.question, {
      fontSize: "34px",
      color: "#333",
    })
    .setOrigin(0.5);

  currentContainer.add([box, title, questionText]);

  let sound = scene.sound.add(q.sound);
  sound.play();

  let soundBtn = scene.add
    .rectangle(800, 50, 300, 50, 0x4fa79d)
    .setInteractive({ useHandCursor: true });

  let soundText = scene.add
    .text(800, 50, "إعادة تشغيل الصوت", {
      fontSize: "25px",
      color: "#ffffff",
    })
    .setOrigin(0.5);

  soundBtn.on("pointerdown", function () {
    sound.play();
  });

  currentContainer.add([soundBtn, soundText]);

  for (let i = 0; i < q.answers.length; i++) {
    let buttonBg = scene.add
      .rectangle(500, 320 + i * 90, 400, 60, 0x4fa79d)
      .setInteractive({ useHandCursor: true });

    let answerText = scene.add
      .text(500, 320 + i * 90, q.answers[i], {
        fontSize: "26px",
        color: "#ffffff",
      })
      .setOrigin(0.5);

    buttonBg.on("pointerdown", function () {
      scene.input.enabled = false;
      sound.stop();

      if (i === q.correct) {
        score++;
        buttonBg.fillColor = 0x4caf50;
        scene.sound.play("correct");
      } else {
        buttonBg.fillColor = 0xe53935;
        scene.sound.play("wrong");
      }

      scene.time.delayedCall(900, function () {
        scene.input.enabled = true;
        questionIndex++;
        showQuestion.call(scene);
      });
    });

    currentContainer.add([buttonBg, answerText]);
  }

  let scoreBg = scene.add.rectangle(200, 50, 300, 40, 0x4fa79d).setOrigin(0.5);
  let scoreText = scene.add
    .text(200, 50, "النقاط: " + score, {
      fontSize: "32px",
      color: "#ffffff",
    })
    .setOrigin(0.5);

  currentContainer.add([scoreBg, scoreText]);
}

function showResult() {
  let scene = this;
  newScreen(scene);

  let box = scene.add
    .rectangle(500, 325, 700, 400, 0xffffff)
    .setStrokeStyle(4, 0x4fa79d);

  let title = scene.add
    .text(500, 250, "انتهت المرحلة " + currentLevel, {
      fontSize: "42px",
      color: "#333",
    })
    .setOrigin(0.5);

  let resultText = scene.add
    .text(
      500,
      320,
      "مجموع نقاطك: " + score + " / " + selectedQuestions.length,
      { fontSize: "34px", color: "#4fa79d" },
    )
    .setOrigin(0.5);

  currentContainer.add([box, title, resultText]);

  if (score === selectedQuestions.length) {
    let success = scene.add
      .text(500, 380, "🎉 أحسنت", {
        fontSize: "32px",
        color: "#E53935",
      })
      .setOrigin(0.5);

    currentContainer.add(success);

    let applause = scene.sound.add("clap");
    applause.play();

    let nextBtn = scene.add
      .rectangle(500, 460, 550, 70, 0x4fa79d)
      .setInteractive({ useHandCursor: true });

    let nextText = scene.add
      .text(
        500,
        460,
        currentLevel === 1 ? " انتقل للمرحلة الثانية" : "إعادة اللعب",
        { fontSize: "28px", color: "#ffffff" },
      )
      .setOrigin(0.5);

    nextBtn.on("pointerdown", function () {
      applause.stop();

      if (currentLevel === 1) {
        currentLevel = 2;
        selectedQuestions = questionsLevel2;
      } else {
        currentLevel = 1;
        selectedQuestions = questionsLevel1;
      }

      score = 0;
      questionIndex = 0;
      showImage.call(scene);
    });

    currentContainer.add([nextBtn, nextText]);
  } else {
    let retryBtn = scene.add
      .rectangle(500, 460, 300, 70, 0x4fa79d)
      .setInteractive({ useHandCursor: true });

    let retryText = scene.add
      .text(500, 460, "إعادة المحاولة", {
        fontSize: "28px",
        color: "#ffffff",
      })
      .setOrigin(0.5);

    retryBtn.on("pointerdown", function () {
      score = 0;
      questionIndex = 0;
      showImage.call(scene);
    });

    currentContainer.add([retryBtn, retryText]);
  }
}
document.getElementById("backBtn").addEventListener("click", () => {
  window.location.href = "../index.php";
});
