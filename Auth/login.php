<?php
declare(strict_types=1);

require_once __DIR__ . '/../Database/helpers.php';
require_once __DIR__ . '/../Database/auth.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$errors = process_login_request($_POST);

if ($errors !== []) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_email'] = trim((string) ($_POST['email'] ?? ''));
    redirect('/That-Copy/Public/login/index.php');
}
