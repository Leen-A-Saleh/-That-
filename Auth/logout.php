<?php
declare(strict_types=1);

require_once __DIR__ . '/../Database/helpers.php';
require_once __DIR__ . '/../Database/auth.php';

start_secure_session();

logout_user();
redirect('/That-Copy/Public/login/index.php');
