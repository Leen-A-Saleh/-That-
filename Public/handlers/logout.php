<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';

start_secure_session();

$loginUrl = '/That-Copy/Public/login/index.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    handle_logout_post($loginUrl);
}

logout_user();
redirect($loginUrl);
