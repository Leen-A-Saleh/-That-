let config = {
    type: Phaser.AUTO,
    width: 800,
    height: 600,
    backgroundColor: "#f8faff",
    parent: "game-container",
    scene: { preload, create, update }
};

let breathSound; 
let circleX = 400;
let circleY = 350;
let game = new Phaser.Game(config);

let circle, circleRadius = 70, holding = false, startTime = 0, attempts = [];
let instructionText, timerText;

function preload() {
    this.load.audio('breath', 'breath.mp3'); 
}

function create() {
    
    circle = this.add.graphics();
    let shape = new Phaser.Geom.Circle(circleX, circleY, circleRadius);
    circle.setInteractive(shape, Phaser.Geom.Circle.Contains);


    breathSound = this.sound.add('breath', { loop: true }); 


    circle.on('pointerdown', () => {
        if (document.getElementById('history-overlay').style.display === 'block') return;
        holding = true;
        startTime = Date.now();
        instructionText.setText("استمر في الضغط");

        if (breathSound && !breathSound.isPlaying) breathSound.play();
    });

    
    this.input.on('pointerup', () => { 
        if (holding) endBreath(); 
        if (breathSound && breathSound.isPlaying) breathSound.stop();
    });

    
    instructionText = this.add.text(400, 80, "اضغط باستمرار للبدء", { 
        fontSize: "24px", fill: "#34495e", fontWeight: "600" 
    }).setOrigin(0.5);

    
    timerText = this.add.text(400, 300, "", { 
        fontSize: "50px", fill: "#ffffff", fontWeight: "800" 
    }).setOrigin(0.5).setDepth(1);

    
    const saved = localStorage.getItem("breathingAttempts");
    if (saved) {
        attempts = JSON.parse(saved);
        updateSessionUI();
    }

    drawCircle();
}

function update() {
    if (holding) {
        if (circleRadius < 230) {
            circleRadius += 1.2;
            circle.input.hitArea.radius = circleRadius;
        }
        let elapsed = Math.floor((Date.now() - startTime) / 1000);
        timerText.setText(elapsed > 0 ? `${elapsed}s` : "");
        drawCircle();
    } else {
        if (circleRadius > 70) {
            circleRadius -= 2.5;
            circle.input.hitArea.radius = circleRadius;
            drawCircle();
        }
        timerText.setText("");
    }
}

function drawCircle() {
    circle.clear();
    circle.fillStyle(0x78C1B8, 0.15);
    circle.fillCircle(circleX, circleY, circleRadius + 12);

    circle.fillStyle(0x78C1B8, 1);
    circle.fillCircle(circleX, circleY, circleRadius);

    circle.lineStyle(4, 0xffffff, 0.8);
    circle.strokeCircle(circleX, circleY, circleRadius);
}

function endBreath() {
    holding = false;
    let duration = Math.floor((Date.now() - startTime) / 1000);
    if (duration >= 1) {
        attempts.push({ duration: duration });
        localStorage.setItem("breathingAttempts", JSON.stringify(attempts));
        updateSessionUI();
    }
    instructionText.setText("اضغط باستمرار للبدء");
}

function updateSessionUI() {
    document.getElementById('session-count').innerText = attempts.length;
}

window.toggleHistoryUI = function() {
    let overlay = document.getElementById('history-overlay');
    if (overlay.style.display === 'block') {
        overlay.style.display = 'none';
    } else {
        overlay.style.display = 'block';
        renderList();
    }
}

function renderList() {
    let list = document.getElementById('history-list');
    list.innerHTML = '';
    attempts.forEach((data, index) => {
        let item = document.createElement('div');
        item.className = 'history-item';
        item.innerHTML = `
            <span> المحاولة ${index + 1}:</span> 
            <span style="color: #78C1B8; font-weight: bold;">${data.duration} ثواني </span>`;
        list.appendChild(item);
    });
    if (attempts.length === 0) {
        list.innerHTML = '<p style="text-align:center; color:#999; padding: 20px;"> لا توجد محاولات بعد</p>';
    }
}

window.clearHistory = function() {
    if (confirm("هل أنت متأكد من مسح جميع المحاولات؟")) {
        attempts = [];
        localStorage.removeItem("breathingAttempts");
        updateSessionUI();
        renderList();
    }
}

document.getElementById('backBtn').addEventListener('click', function() {
  window.location.href = '../index.php'; 
 });