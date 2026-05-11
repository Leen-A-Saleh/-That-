<?php

declare(strict_types=1);

/**
 * Admin area gate: session must exist and role must be ADMIN.
 * Include this before any output or POST/AJAX handlers.
 */

require_once __DIR__ . '/../../Database/auth.php';

start_secure_session();
require_admin();
