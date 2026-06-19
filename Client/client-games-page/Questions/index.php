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
<html lang="ar">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>لعبة الذاكرة الممتعة</title>

    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.js"></script>
  </head>

  <body>
    <button
      id="backBtn"
      style="
        position: fixed;
        top: 20px;
        left: 30px;
        padding: 20px 30px;
        background-color: #e8f6ff;
        color: #78c1b8;
        border: 2px solid #78c1b8;
        border-radius: 30px;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
      "
    >
      رجوع
    </button>
    <div id="game-container"></div>

    <script src="main.js?v=<?= e($mainScriptVersion) ?>"></script>
  </body>
</html>