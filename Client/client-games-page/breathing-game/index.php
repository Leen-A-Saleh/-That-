<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/client.php';

start_secure_session();
require_auth();
require_role(['CLIENT']);

$mainScriptVersion = (string) filemtime(__DIR__ . '/main.js');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تمرين التنفس الاحترافي</title>
    <script src="https://cdn.jsdelivr.net/npm/phaser@3/dist/phaser.js"></script>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>

<div id="game-container">
    <div class="ui-header">
        <div class="session-badge history-button">
            الجلسات : <span id="session-count">0</span>
        </div>
        <button class="history-button" onclick="toggleHistoryUI()">📜 السجل</button>
    </div>

    <div id="history-overlay">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 20px; font-weight: 800; color: #2c3e50;">سجل النشاط</span>
            <span class="close-btn" onclick="toggleHistoryUI()">×</span>
        </div>
        
        <div id="history-list"></div>

        <button id="clear-btn" onclick="clearHistory()">حذف السجل</button>
    </div>
    <button id="backBtn" >رجوع</button>
</div>

<!-- نظام التنبيهات الموحّد SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/That-Copy/Public/js/sweet-alerts.js"></script>
<script src="main.js?v=<?= e($mainScriptVersion) ?>"></script>
</body>
</html>
