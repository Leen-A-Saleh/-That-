<?php

declare(strict_types=1);

/**
 * Dedicated logout target so sidebar forms are not swallowed by per-page POST / JSON handlers
 * (e.g. admin-therapist-page/therapist.php).
 */

require_once __DIR__ . '/require-admin.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'logout') {
  handle_logout_post('/That-Copy/Auth/login/index.php');
}

redirect('/That-Copy/Auth/login/index.php');
