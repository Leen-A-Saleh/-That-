let config = {
  type: Phaser.AUTO,
  width: 1200,
  height: 600,
  backgroundColor: "#cceeff",
  parent: "game-container",
  scale: { mode: Phaser.Scale.FIT, autoCenter: Phaser.Scale.CENTER_BOTH },
  scene: { preload, create },
};

let game = new Phaser.Game(config);

let currentLevel = 0;
let bgMusic;

let levels = [
  {
    name: "المرحلة الأولى",
    imgL: "L1",
    imgR: "R1",
    pathL: "./assests/left.jpg",
    pathR: "./assests/right.jpg",
    diffs: [
      {
        pts: [
          { x: 736.6, y: 270.8 },
          { x: 149.17, y: 276.62 },
        ],
      },
      {
        pts: [
          { x: 947, y: 231.6 },
          { x: 388.23, y: 223.79 },
        ],
      },
      {
        pts: [
          { x: 1132, y: 489 },
          { x: 509.63, y: 484.76 },
        ],
      },
    ],
  },
  {
    name: "المرحلة الثانية",
    imgL: "L2",
    imgR: "R2",
    pathL: "./assests/left1.jpg",
    pathR: "./assests/right1.jpg",
    diffs: [
      {
        pts: [
          { x: 290, y: 403 },
          { x: 892, y: 402 },
        ],
      },
      {
        pts: [
          { x: 715, y: 313 },
          { x: 120, y: 333 },
        ],
      },
      {
        pts: [
          { x: 478, y: 196 },
          { x: 1074, y: 195 },
        ],
      },
      {
        pts: [
          { x: 526, y: 452 },
          { x: 1138, y: 409 },
        ],
      },
      {
        pts: [
          { x: 113, y: 230 },
          { x: 708, y: 228 },
        ],
      },
    ],
  },
];

let foundCount;
let timerText;
let timerEvent;
let isGameOver;
let timeLeft;

function preload() {
  levels.forEach((l) => {
    this.load.image(l.imgL, l.pathL);
    this.load.image(l.imgR, l.pathR);
  });

  this.load.audio("success", "./assests/success.mp3");
  this.load.audio("win", "./assests/win.mp3");
  this.load.audio("bgMusic", "./assests/bgMusic.mp3");
}

function create() {
  if (!bgMusic) {
    bgMusic = this.sound.add("bgMusic", {
      loop: true,
      volume: 0.2,
    });
    bgMusic.play();
  }

  startLevel.call(this);
}

function startLevel() {
  this.children.removeAll();
  this.input.removeAllListeners();
  if (timerEvent) timerEvent.remove();

  let data = levels[currentLevel];
  data.diffs.forEach((d) => (d.found = false));

  isGameOver = false;
  foundCount = 0;
  timeLeft = 20;

  let halfWidth = 600;
  let centerY = 300;

  this.add
    .image(0, centerY, data.imgL)
    .setOrigin(0, 0.5)
    .setDisplaySize(halfWidth, 400);

  this.add
    .image(halfWidth, centerY, data.imgR)
    .setOrigin(0, 0.5)
    .setDisplaySize(halfWidth, 400);

  let centerX = 600;
  this.add.rectangle(centerX, 45, 400, 50, 0x1e678c).setOrigin(0.5);

  timerText = this.add
    .text(centerX, 45, `${data.name} | الوقت: ${timeLeft}`, {
      fontSize: "24px",
      fill: "#fff",
      fontStyle: "bold",
      fontFamily: "Arial",
    })
    .setOrigin(0.5);

  timerEvent = this.time.addEvent({
    delay: 1000,
    loop: true,
    callback: () => {
      if (isGameOver) return;
      timeLeft--;
      timerText.setText(`${data.name} | الوقت: ${timeLeft}`);
      if (timeLeft <= 0) showLoss.call(this);
    },
  });

  this.input.on("pointerdown", (pointer) => {
    if (isGameOver) return;

    data.diffs.forEach((d) => {
      if (d.found) return;

      let hit = d.pts.some(
        (pt) =>
          Phaser.Math.Distance.Between(
            pointer.worldX,
            pointer.worldY,
            pt.x,
            pt.y,
          ) < 50,
      );

      if (hit) {
        d.found = true;
        foundCount++;

        d.pts.forEach((pt) => {
          this.add
            .circle(pt.x, pt.y, 50, 0x00ff00, 0.4)
            .setStrokeStyle(3, 0x00ff00);
        });

        if (this.sound.get("success")) {
          this.sound.play("success", { volume: 0.3 });
        }

        if (foundCount >= data.diffs.length) {
          showWin.call(this);
        }
      }
    });
  });
}

function showWin() {
  isGameOver = true;
  if (timerEvent) timerEvent.remove();

  if (bgMusic) {
    this.tweens.add({
      targets: bgMusic,
      volume: 0.05,
      duration: 300,
    });
  }

  let winSound = this.sound.add("win");
  winSound.play({ volume: 0.6 });

  let usedTime = 15 - timeLeft;

  let isLastLevel = currentLevel === levels.length - 1;

  if (isLastLevel) {
    let centerX = 600;
    let centerY = 300;

    this.add.rectangle(centerX, centerY, 1200, 600, 0x000000, 0.6);

    this.add
      .rectangle(centerX, centerY, 550, 300, 0xffffff)
      .setStrokeStyle(8, 0x1e678c);

    this.add
      .text(centerX, centerY - 80, "🏆 مبروك! أنهيت اللعبة", {
        fontSize: "38px",
        fill: "#1e678c",
        fontStyle: "bold",
      })
      .setOrigin(0.5);

    this.add
      .text(centerX, centerY - 10, ` الوقت المستغرق: ${usedTime} ثانية`, {
        fontSize: "30px",
        fill: "#000",
      })
      .setOrigin(0.5);

    let button = this.add
      .text(centerX, centerY + 80, "إعادة اللعبة", {
        fontSize: "28px",
        fill: "#fff",
        backgroundColor: "#1e678c",
        padding: { x: 30, y: 15 },
      })
      .setOrigin(0.5)
      .setInteractive({ useHandCursor: true });

    button.on("pointerdown", () => {
      currentLevel = 0;
      startLevel.call(this);
    });
  } else {
    if (bgMusic) {
      this.time.delayedCall(2000, () => {
        this.tweens.add({
          targets: bgMusic,
          volume: 0.2,
          duration: 500,
        });
      });
    }

    let canGoNext = true;
    showOverlay.call(this, "🎉 أحسنت", 0x1e678c, canGoNext);
  }
}

function showLoss() {
  isGameOver = true;
  if (timerEvent) timerEvent.remove();

  levels[currentLevel].diffs.forEach((d) => {
    if (!d.found) {
      d.pts.forEach((pt) => {
        this.add
          .circle(pt.x, pt.y, 50, 0xff0000, 0.5)
          .setStrokeStyle(3, 0xff0000);
      });
    }
  });

  this.time.delayedCall(500, () => {
    let centerX = 600;
    let centerY = 300;

    this.add
      .rectangle(centerX, centerY, 500, 250, 0xcceeff)
      .setStrokeStyle(6, 0x1e678c);

    this.add
      .text(centerX, centerY - 40, "انتهى الوقت", {
        fontSize: "45px",
        fill: "#000",
        fontStyle: "bold",
      })
      .setOrigin(0.5);

    let button = this.add
      .text(centerX, centerY + 60, "إعادة اللعب", {
        fontSize: "28px",
        fill: "#fff",
        backgroundColor: "#1e678c",
        padding: { x: 25, y: 12 },
      })
      .setOrigin(0.5)
      .setInteractive({ useHandCursor: true });

    button.on("pointerdown", () => {
      startLevel.call(this);
    });
  });
}

function showOverlay(message, color, canGoNext) {
  let centerX = 600;
  let centerY = 300;

  this.add.rectangle(centerX, centerY, 1200, 600, 0x000000, 0.6);
  this.add
    .rectangle(centerX, centerY, 500, 250, 0xcceeff)
    .setStrokeStyle(6, color);

  this.add
    .text(centerX, centerY - 40, message, {
      fontSize: "45px",
      fill: "#000",
      fontStyle: "bold",
    })
    .setOrigin(0.5);

  let buttonText = canGoNext ? "المرحلة التالية" : "إعادة اللعب";

  let button = this.add
    .text(centerX, centerY + 60, buttonText, {
      fontSize: "28px",
      fill: "#fff",
      backgroundColor: "#1e678c",
      padding: { x: 25, y: 12 },
    })
    .setOrigin(0.5)
    .setInteractive({ useHandCursor: true });

  button.on("pointerdown", () => {
    if (canGoNext) {
      currentLevel++;
      startLevel.call(this);
    } else {
      startLevel.call(this);
    }
  });
}
document.getElementById('backBtn').addEventListener('click', function() {
  window.location.href = '../index.php'; 
 });
 document.getElementById('backBtn').addEventListener('click', () => {
  window.location.href = '../index.php';
});