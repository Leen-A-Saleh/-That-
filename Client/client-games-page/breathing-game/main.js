let config = {
    type: Phaser.AUTO,
    width: 800,
    height: 600,
    backgroundColor: "#f8faff",
    parent: "game-container",
    scene: { preload, create, update }
};

const API_URL = "breath-database.php";

let breathSound; 
let circleX = 400;
let circleY = 350;
let game = new Phaser.Game(config);

let circle, circleRadius = 70, holding = false, startTime = 0;
let instructionText, timerText;
let currentSessionId = null;
let creatingSession = false;
let completingSessionIds = new Set();
let pointerIsDown = false;

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
        startBreathSession();
    });

    
    this.input.on('pointerup', () => {
        pointerIsDown = false;
        if (holding) endBreath(); 
        if (breathSound && breathSound.isPlaying) breathSound.stop();
    });

    
    instructionText = this.add.text(400, 80, "اضغط باستمرار للبدء", { 
        fontSize: "24px", fill: "#34495e", fontWeight: "600" 
    }).setOrigin(0.5);

    
    timerText = this.add.text(400, 300, "", { 
        fontSize: "50px", fill: "#ffffff", fontWeight: "800" 
    }).setOrigin(0.5).setDepth(1);

    
    updateSessionUI();

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

async function startBreathSession() {
    if (holding || creatingSession) return;

    pointerIsDown = true;
    creatingSession = true;
    instructionText.setText("جاري بدء الجلسة...");

    try {
        const data = await breathingApi("create", {}, "POST");
        currentSessionId = data.session_id;

        if (!pointerIsDown) {
            currentSessionId = null;
            instructionText.setText("اضغط باستمرار للبدء");
            return;
        }

        holding = true;
        startTime = Date.now();
        instructionText.setText("استمر في الضغط");

        if (breathSound && !breathSound.isPlaying) breathSound.play();
    } catch (error) {
        console.error(error);
        instructionText.setText("تعذر بدء الجلسة، حاول مرة أخرى");
    } finally {
        creatingSession = false;
    }
}

async function endBreath() {
    holding = false;
    let duration = Math.floor((Date.now() - startTime) / 1000);
    let sessionId = currentSessionId;
    currentSessionId = null;

    let savedSuccessfully = true;

    if (duration >= 1 && sessionId && !completingSessionIds.has(sessionId)) {
        completingSessionIds.add(sessionId);

        try {
            await breathingApi("complete", {
                session_id: sessionId,
                duration: duration
            }, "POST");
            await updateSessionUI();

            if (document.getElementById('history-overlay').style.display === 'block') {
                await renderList();
            }
        } catch (error) {
            savedSuccessfully = false;
            console.error(error);
            instructionText.setText("انتهت الجلسة، لكن تعذر حفظها");
        } finally {
            completingSessionIds.delete(sessionId);
        }
    }

    if (savedSuccessfully) {
        instructionText.setText("اضغط باستمرار للبدء");
    }
}

async function updateSessionUI() {
    try {
        const data = await breathingApi("count");
        document.getElementById('session-count').innerText = data.count;
    } catch (error) {
        console.error(error);
        document.getElementById('session-count').innerText = "0";
    }
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

async function renderList() {
    let list = document.getElementById('history-list');
    list.innerHTML = '<p style="text-align:center; color:#999; padding: 20px;">جاري تحميل السجل...</p>';

    let sessions = [];
    try {
        const data = await breathingApi("history");
        sessions = data.sessions || [];
    } catch (error) {
        console.error(error);
        list.innerHTML = '<p style="text-align:center; color:#999; padding: 20px;">تعذر تحميل السجل</p>';
        return;
    }

    list.innerHTML = '';
    sessions.forEach((data, index) => {
        let item = document.createElement('div');
        item.className = 'history-item';
        let playedAt = data.played_at ? `<small style="color:#777;">${escapeHtml(data.played_at)}</small>` : "";
        item.innerHTML = `
            <span> المحاولة ${index + 1}:</span> 
            <span style="color: #78C1B8; font-weight: bold;">${data.duration} ثواني ${playedAt}</span>`;
        list.appendChild(item);
    });
    if (sessions.length === 0) {
        list.innerHTML = '<p style="text-align:center; color:#999; padding: 20px;"> لا توجد محاولات بعد</p>';
    }
}

window.clearHistory = function() {
    showConfirm("هل أنت متأكد من حذف جميع محاولات التنفس؟", { icon: "warning" }).then(function (ok) {
        if (ok) deleteBreathingHistory();
    });
}

async function deleteBreathingHistory() {
    try {
        await breathingApi("delete", {}, "POST");
        await updateSessionUI();
        await renderList();
    } catch (error) {
        console.error(error);
        showErrorAlert("تعذر حذف السجل، حاول مرة أخرى");
    }
}

async function breathingApi(action, payload = {}, method = "GET") {
    const options = { method };
    options.credentials = "same-origin";
    options.cache = "no-store";

    let url = `${API_URL}?action=${encodeURIComponent(action)}&_=${Date.now()}`;

    if (method === "POST") {
        options.headers = { "Content-Type": "application/json" };
        options.body = JSON.stringify(payload);
    }

    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok || !data.success) {
        throw new Error(data.message || "Breathing API request failed.");
    }

    return data;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.getElementById('backBtn').addEventListener('click', function() {
  window.location.href = '../index.php'; 
 });
