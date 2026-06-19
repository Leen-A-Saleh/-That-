<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/client.php';

start_secure_session();
require_auth();
require_role(['CLIENT']);

$mainScriptVersion = (string) filemtime(__DIR__ . '/misplacedpin.js');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>أوجد الاختلافات</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>أوجد الاختلافات</h1>
<div id="level">Level 1</div>
<div id="score">Points: 0</div>
<button type="button" onclick="showHistory()">السجل</button>

<div class="board">
  <div id="left" class="side"></div>
  <div id="right" class="side"></div>
</div>

<button onclick="restartGame()">اعادة اللعبة</button>

<div id="gameOverModal" class="modal">
  <div class="modal-content">
    <h2> Game Over!</h2>
    <p id="saveStatus"></p>
    <button onclick="retryGame()">اعادة اللعبة</button>
  </div>
</div>
<div id="historyModal" class="modal">
  <div class="modal-content">
    <h2>السجل</h2>
    <div id="historyList"></div>
    <button type="button" onclick="hideHistory()">إغلاق</button>
  </div>
</div>
<button id="backBtn" >رجوع</button>
<script src="misplacedpin.js?v=<?= e($mainScriptVersion) ?>"></script>
</body>
</html>
