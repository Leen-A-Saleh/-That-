<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/client.php';

start_secure_session();
require_auth();
require_role(['CLIENT']);

$mainScriptVersion = (string) filemtime(__DIR__ . '/main.js');
?>
<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>لعبة الذاكرة</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <h1>لعبة الذاكرة</h1>
    <div class="controls">
      الوقت: <span id="time">0</span> ثانية | المحاولات:
      <span id="moves">0</span>
    </div>
    <select id="difficulty">
      <option value="4">سهل</option>
      <option value="6">متوسط</option>
    </select>
    <button id="restartBtn">إعادة تشغيل</button>
    <button id="historyBtn">السجل</button>
    <button id="backBtn">رجوع</button>

    <div class="game" id="game"></div>
    <div id="result"></div>
    <div id="winPopup" class="popup">
      <div class="popup-box">
        <h2>🎉 أحسنت</h2>

        <p id="finalTime"></p>
        <p id="finalMoves"></p>
        <p id="saveStatus"></p>

        <button id="playAgainBtn">إعادة اللعب</button>
      </div>
    </div>
    <div id="historyPopup" class="popup">
      <div class="popup-box">
        <h2>السجل</h2>
        <div id="historyList"></div>
        <button id="closeHistoryBtn">إغلاق</button>
      </div>
    </div>
    <script src="main.js?v=<?= e($mainScriptVersion) ?>"></script>
  </body>
</html>
