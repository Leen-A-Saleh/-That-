<?php

declare(strict_types=1);

require_once __DIR__ . '/require-admin.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    handle_logout_post('/That-Copy/Public/login/index.php');
}

redirect('/That-Copy/Public/login/index.php');
