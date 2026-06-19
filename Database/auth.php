<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

// ─── Login

function login_user(string $email, string $password): bool
{
    $email = trim($email);
    if ($email === '' || $password === '') {
        return false;
    }

    $stmt = db()->prepare(
        'SELECT user_id, name, username, email, password, role, is_active, avatar
         FROM users
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || (int) ($user['is_active'] ?? 1) !== 1) {
        return false;
    }

    if (!password_verify($password, (string) $user['password'])) {
        return false;
    }

    session_regenerate_id(true);
    $avatarRaw = trim((string) ($user['avatar'] ?? ''));
    $_SESSION['auth'] = [
        'user_id' => (int) $user['user_id'],
        'name' => (string) $user['name'],
        'username' => (string) $user['username'],
        'email' => (string) $user['email'],
        'role' => strtoupper((string) $user['role']),
        'avatar' => $avatarRaw !== '' ? $avatarRaw : null,
        'logged_at' => time(),
    ];
    $_SESSION['user_id'] = $_SESSION['auth']['user_id'];
    $_SESSION['user_name'] = $_SESSION['auth']['name'];
    $_SESSION['user_email'] = $_SESSION['auth']['email'];
    $_SESSION['user_role'] = $_SESSION['auth']['role'];

    record_login_activity((int) $user['user_id']);
    bind_user_session_version((int) $user['user_id']);

    return true;
}

// ─── Login Activity

function request_client_ip(): string
{
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = trim((string) $_SERVER['HTTP_CF_CONNECTING_IP']);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        foreach (explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']) as $part) {
            $ip = trim($part);
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    $remote = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    return $remote !== '' ? $remote : '0.0.0.0';
}

/**
 * @return array{browser: string, os: string}
 */
function parse_login_user_agent(string $userAgent): array
{
    $ua = $userAgent !== '' ? $userAgent : 'Unknown';
    $browser = 'Unknown';
    $os = 'Unknown';

    if (preg_match('/Windows NT 10\.0/i', $ua)) {
        $os = 'Windows 10/11';
    } elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
        $os = 'Windows 8.1';
    } elseif (preg_match('/Windows NT 6\.2/i', $ua)) {
        $os = 'Windows 8';
    } elseif (preg_match('/Windows NT 6\.1/i', $ua)) {
        $os = 'Windows 7';
    } elseif (preg_match('/Windows/i', $ua)) {
        $os = 'Windows';
    } elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) {
        $os = 'macOS ' . str_replace('_', '.', $m[1]);
    } elseif (preg_match('/Android ([\d.]+)/i', $ua, $m)) {
        $os = 'Android ' . $m[1];
    } elseif (preg_match('/(?:CPU )?iPhone OS ([\d_]+)/i', $ua, $m)) {
        $os = 'iOS ' . str_replace('_', '.', $m[1]);
    } elseif (preg_match('/Linux/i', $ua)) {
        $os = 'Linux';
    }

    if (preg_match('/Edg(?:e|A|iOS)?\/([\d.]+)/i', $ua, $m)) {
        $browser = 'Edge ' . $m[1];
    } elseif (preg_match('/OPR\/([\d.]+)/i', $ua, $m)) {
        $browser = 'Opera ' . $m[1];
    } elseif (preg_match('/CriOS\/([\d.]+)/i', $ua, $m)) {
        $browser = 'Chrome (iOS) ' . $m[1];
    } elseif (preg_match('/Chrome\/([\d.]+)/i', $ua, $m) && !preg_match('/Edg|OPR/i', $ua)) {
        $browser = 'Chrome ' . $m[1];
    } elseif (preg_match('/Firefox\/([\d.]+)/i', $ua, $m)) {
        $browser = 'Firefox ' . $m[1];
    } elseif (preg_match('/Version\/([\d.]+).*Safari/i', $ua, $m) && preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) {
        $browser = 'Safari ' . $m[1];
    } elseif (preg_match('/MSIE ([\d.]+)/i', $ua, $m) || preg_match('/Trident\/.*rv:([\d.]+)/i', $ua, $m)) {
        $browser = 'Internet Explorer ' . $m[1];
    }

    return [
        'browser' => mb_substr($browser, 0, 100),
        'os' => mb_substr($os, 0, 100),
    ];
}

/**
 * @return array{country: ?string, city: ?string}
 */
function geolocate_ip_for_login(string $ip): array
{
    if ($ip === '' || $ip === '0.0.0.0') {
        return ['country' => null, 'city' => null];
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return ['country' => null, 'city' => null];
    }

    $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,city';
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 1.5,
            'ignore_errors' => true,
        ],
    ]);
    $json = @file_get_contents($url, false, $ctx);
    if ($json === false) {
        return ['country' => null, 'city' => null];
    }
    $data = json_decode($json, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
        return ['country' => null, 'city' => null];
    }

    $country = isset($data['country']) ? trim((string) $data['country']) : '';
    $city = isset($data['city']) ? trim((string) $data['city']) : '';

    return [
        'country' => $country !== '' ? mb_substr($country, 0, 100) : null,
        'city' => $city !== '' ? mb_substr($city, 0, 100) : null,
    ];
}

function ensure_login_activities_table(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $ensured = true;

    db()->exec(
        'CREATE TABLE IF NOT EXISTS login_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            login_date DATE NOT NULL,
            login_time TIME NOT NULL,
            browser VARCHAR(100) NULL,
            os VARCHAR(100) NULL,
            ip_address VARCHAR(45) NULL,
            country VARCHAR(100) NULL,
            city VARCHAR(100) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_login_user (user_id),
            INDEX idx_login_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function record_login_activity(int $userId): void
{
    try {
        ensure_login_activities_table();

        $ip = request_client_ip();
        $ua = parse_login_user_agent((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $geo = geolocate_ip_for_login($ip);
        $now = new DateTimeImmutable('now');
        $stmt = db()->prepare(
            'INSERT INTO login_activities
                (user_id, login_date, login_time, browser, os, ip_address, country, city)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $now->format('Y-m-d'),
            $now->format('H:i:s'),
            $ua['browser'],
            $ua['os'],
            mb_substr($ip, 0, 45),
            $geo['country'],
            $geo['city'],
        ]);
    } catch (\Throwable $e) {
        // Do not block sign-in if logging fails
    }
}

// ─── Logout

function users_table_columns(bool $refresh = false): array
{
    static $columns = null;

    if ($refresh) {
        $columns = null;
    }

    if ($columns === null) {
        try {
            $stmt = db()->query('SHOW COLUMNS FROM users');
            $columns = array_map(
                static fn(array $row): string => (string) ($row['Field'] ?? ''),
                $stmt->fetchAll()
            );
        } catch (\Throwable $e) {
            $columns = [];
        }
    }

    return $columns;
}

function users_has_column(string $column): bool
{
    return in_array($column, users_table_columns(), true);
}

function ensure_users_session_version_column(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $ensured = true;

    if (users_has_column('session_version')) {
        return;
    }

    try {
        db()->exec('ALTER TABLE users ADD COLUMN session_version INT UNSIGNED NOT NULL DEFAULT 0');
        users_table_columns(true);
    } catch (\Throwable $e) {
    }
}

function get_user_session_version(int $userId): int
{
    if ($userId <= 0) {
        return 0;
    }

    ensure_users_session_version_column();

    if (!users_has_column('session_version')) {
        return 0;
    }

    try {
        $stmt = db()->prepare('SELECT session_version FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return (int) ($row['session_version'] ?? 0);
    } catch (\Throwable $e) {
        return 0;
    }
}

function bind_user_session_version(int $userId): void
{
    if ($userId <= 0 || !isset($_SESSION['auth']) || !is_array($_SESSION['auth'])) {
        return;
    }

    $_SESSION['auth']['session_version'] = get_user_session_version($userId);
}

function invalidate_all_user_sessions(int $userId): bool
{
    if ($userId <= 0) {
        return false;
    }

    ensure_users_session_version_column();

    if (!users_has_column('session_version')) {
        return false;
    }

    try {
        $stmt = db()->prepare(
            'UPDATE users SET session_version = session_version + 1 WHERE user_id = ? LIMIT 1'
        );
        $stmt->execute([$userId]);

        return $stmt->rowCount() >= 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function assert_valid_user_session(): void
{
    if (!is_authenticated()) {
        return;
    }

    ensure_users_session_version_column();

    if (!users_has_column('session_version')) {
        return;
    }

    $userId = (int) ($_SESSION['auth']['user_id'] ?? 0);
    if ($userId <= 0) {
        return;
    }

    $currentVersion = get_user_session_version($userId);

    if (!isset($_SESSION['auth']['session_version'])) {
        $_SESSION['auth']['session_version'] = $currentVersion;

        return;
    }

    $sessionVersion = (int) $_SESSION['auth']['session_version'];
    if ($sessionVersion === $currentVersion) {
        return;
    }

    logout_user();

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && stripos((string) $_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') !== false;

    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => 'انتهت الجلسة. يرجى تسجيل الدخول مجدداً.',
            'redirect' => '/That-Copy/Public/login/index.php',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    redirect('/That-Copy/Public/login/index.php');
}

function logout_user_from_all_devices(int $userId): bool
{
    return invalidate_all_user_sessions($userId);
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function handle_logout_post(?string $redirectTo = null): void
{
    start_secure_session();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    if (($_POST['action'] ?? null) !== 'logout') {
        return;
    }

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'انتهت صلاحية الطلب. أعد المحاولة.');
        redirect($_SERVER['REQUEST_URI'] ?? '/');
    }

    logout_user();

    redirect($redirectTo ?: '/That-Copy/Public/login/index.php');
}

// ─── Session Helpers

function current_user(): ?array
{
    return $_SESSION['auth'] ?? null;
}

function is_authenticated(): bool
{
    return isset($_SESSION['auth']['user_id']);
}

function require_auth(): void
{
    if (!is_authenticated()) {
        redirect('/That-Copy/Public/login/index.php');
    }

    assert_valid_user_session();
}

function require_role(array $roles): void
{
    require_auth();
    $userRole = strtoupper((string) ($_SESSION['auth']['role'] ?? ''));
    $allowed = array_map(static fn(string $r): string => strtoupper($r), $roles);

    if (!in_array($userRole, $allowed, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function require_admin(): void
{
    require_auth();
    $userRole = strtoupper((string) ($_SESSION['auth']['role'] ?? ''));
    if ($userRole !== 'ADMIN') {
        redirect(role_home_path($userRole));
    }
}

// ─── Existence Checks

function username_exists(string $username): bool
{
    $stmt = db()->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([trim($username)]);
    return (bool) $stmt->fetchColumn();
}

function email_exists(string $email): bool
{
    $stmt = db()->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([trim($email)]);
    return (bool) $stmt->fetchColumn();
}

// ─── Role Routing

function role_home_path(string $role): string
{
    $role = strtoupper($role);
    if ($role === 'ADMIN') {
        return '/That-Copy/Admin/admin-dashboard-page/admin-dashboard.php';
    }
    if ($role === 'THERAPIST') {
        return '/That-Copy/Therapist/therapist/therapist-dashboard/index.php';
    }
    return '/That-Copy/Client/client-dashboard-page/index.php';
}


// ─── Login Handler

function process_login_request(array $post): array
{
    $errors = [];

    if (!verify_csrf($post['csrf_token'] ?? null)) {
        $errors[] = 'انتهت صلاحية الطلب. أعد المحاولة.';
        return $errors;
    }

    $email = trim((string) ($post['email'] ?? ''));
    $password = (string) ($post['password'] ?? '');

    if ($email === '') {
        $errors[] = 'البريد الإلكتروني مطلوب.';
        return $errors;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صالح.';
        return $errors;
    }

    if (!login_user($email, $password)) {
        $errors[] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
        return $errors;
    }

    $role = strtoupper((string) ($_SESSION['auth']['role'] ?? 'CLIENT'));
    redirect(role_home_path($role));
    return [];
}

// ─── Signup

function process_full_signup(array $post): array
{
    $errors = [];

    if (!verify_csrf($post['csrf_token'] ?? null)) {
        return ['انتهت صلاحية الطلب، يرجى المحاولة مرة أخرى.'];
    }

    // Personal info
    $fullName = trim((string) ($post['fullName'] ?? ''));
    $email = trim((string) ($post['email'] ?? ''));
    $phone = trim((string) ($post['phone'] ?? ''));
    $username = trim((string) ($post['username'] ?? ''));
    $password = (string) ($post['password'] ?? '');
    $confirmPassword = (string) ($post['confirmPassword'] ?? '');

    // Additional Registration Fields
    $gender = trim((string) ($post['gender'] ?? ''));
    $age = (int) ($post['age'] ?? 0);
    $hasGuardian = (int) ($post['has_guardian'] ?? 0);
    $guardianName = trim((string) ($post['guardian_name'] ?? ''));
    $guardianPhone = trim((string) ($post['guardian_phone'] ?? ''));

    // Client preferences
    $treatmentType = trim((string) ($post['treatment_type'] ?? ''));
    $sessionType = trim((string) ($post['session_type'] ?? 'BOTH'));
    $sessionTime = trim((string) ($post['session_time'] ?? 'FLEXIBLE'));

    // Survey fields
    $surveyTreatmentType = trim((string) ($post['survey_treatment_type'] ?? ''));
    $surveySymptoms = trim((string) ($post['survey_symptoms'] ?? ''));
    $surveyRepeatedSymptoms = trim((string) ($post['survey_repeated_symptoms'] ?? ''));
    $surveyPrevTherapy = trim((string) ($post['survey_prev_therapy'] ?? ''));
    $surveyAge = (int) ($post['survey_age'] ?? 0);
    $surveyGender = trim((string) ($post['survey_gender'] ?? ''));
    $surveyNationality = trim((string) ($post['survey_nationality'] ?? ''));
    $surveyTherapistGender = trim((string) ($post['survey_therapist_gender'] ?? ''));
    $surveyFamilyHistory = trim((string) ($post['survey_family_history'] ?? ''));
    $surveyPhysicalIssues = trim((string) ($post['survey_physical_issues'] ?? ''));
    $surveyPhysicalDetails = trim((string) ($post['survey_physical_details'] ?? ''));
    $surveyMaritalStatus = trim((string) ($post['survey_marital_status'] ?? ''));
    $surveyEducationLevel = trim((string) ($post['survey_education_level'] ?? ''));
    $surveySmoking = trim((string) ($post['survey_smoking'] ?? ''));
    $surveyAlcohol = trim((string) ($post['survey_alcohol'] ?? ''));
    $surveyDrugs = trim((string) ($post['survey_drugs'] ?? ''));
    $surveyContactPreference = trim((string) ($post['survey_contact_preference'] ?? ''));
    $surveyParentalConsent = trim((string) ($post['survey_parental_consent'] ?? ''));

    // Validation
    if ($fullName === '') {
        $errors[] = 'الاسم الكامل مطلوب.';
    }
    if ($email === '') {
        $errors[] = 'البريد الإلكتروني مطلوب.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صالح.';
    }
    if ($gender !== 'M' && $gender !== 'F') {
        $errors[] = 'يرجى تحديد الجنس.';
    }
    if ($age > 0 && $age < 18) {
        if ($hasGuardian !== 1) {
            $errors[] = 'لا يمكن إنشاء حساب لمن هم أقل من 18 سنة إلا بوجود أحد الوالدين.';
        } elseif ($guardianName === '' || $guardianPhone === '') {
            $errors[] = 'يجب إدخال اسم ورقم هاتف ولي الأمر.';
        }
    }
    if ($phone === '') {
        $errors[] = 'رقم الهاتف مطلوب.';
    }
    if ($username === '') {
        $errors[] = 'اسم المستخدم مطلوب.';
    } elseif (username_exists($username)) {
        $errors[] = 'اسم المستخدم مستخدم مسبقاً.';
    }
    if (email_exists($email)) {
        $errors[] = 'البريد الإلكتروني مسجل مسبقاً.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'كلمتا المرور غير متطابقتين.';
    }
    // قاعدة العمر: يُمنع إنشاء حساب لمن هم أقل من 18 سنة إلا بموافقة وحضور أحد الوالدين
    // (تحقق خادمي حاسم؛ يُسمح فقط عند parental_consent = YES القادمة من سؤال موافقة الوالدين)
    if ($surveyAge > 0 && $surveyAge < 18 && $surveyParentalConsent !== 'YES') {
        $errors[] = 'لا يمكن إنشاء حساب لمن هم أقل من 18 سنة إلا بوجود وموافقة أحد الوالدين.';
    }
    
    // Clear guardian info if age is 18 or older
    if ($age >= 18) {
        $hasGuardian = 0;
        $guardianName = null;
        $guardianPhone = null;
    }
    
    if ($errors !== []) {
        return $errors;
    }

    $prevTherapyBool = ($surveyPrevTherapy === 'YES') ? 1 : 0;

    try {
        db()->beginTransaction();

        // 1. Insert survey
        $stmt = db()->prepare(
            'INSERT INTO client_surveys
                (treatment_type, symptoms, repeated_symptoms, prev_therapy, age, gender, nationality,
                 therapist_gender, family_history, physical_issues, physical_details, marital_status,
                 education_level, smoking, alcohol, drugs, contact_preference)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $surveyTreatmentType,
            $surveySymptoms,
            $surveyRepeatedSymptoms,
            $prevTherapyBool,
            $surveyAge,
            $surveyGender,
            $surveyNationality,
            $surveyTherapistGender,
            $surveyFamilyHistory,
            $surveyPhysicalIssues,
            $surveyPhysicalDetails,
            $surveyMaritalStatus,
            $surveyEducationLevel,
            $surveySmoking,
            $surveyAlcohol,
            $surveyDrugs,
            $surveyContactPreference,
        ]);
        $surveyId = (int) db()->lastInsertId();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare(
            "INSERT INTO users (name, email, phone, username, password, role, gender, has_guardian, guardian_name, guardian_phone)
             VALUES (?, ?, ?, ?, ?, 'CLIENT', ?, ?, ?, ?)"
        );
        $stmt->execute([$fullName, $email, $phone, $username, $hashedPassword, $gender, $hasGuardian, $guardianName, $guardianPhone]);
        $userId = (int) db()->lastInsertId();

        $clientSql = 'INSERT INTO clients (client_id, survey_id, gender, treatment_type, preferred_session_type, preferred_session_time)
                      VALUES (?, ?, ?, ?, ?, ?)';
        db()->prepare($clientSql)->execute([
            $userId, $surveyId, $surveyGender, $treatmentType, $sessionType, $sessionTime,
        ]);

        db()->commit();

        set_flash('success', 'تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.');
        return [];
    } catch (\Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        return ['حدث خطأ أثناء التسجيل، حاول مرة أخرى.'];
    }
}

// ─── Forgot Password

function process_forgot_password_request(array $post): array
{
    $errors = [];

    if (!verify_csrf($post['csrf_token'] ?? null)) {
        $errors[] = 'انتهت صلاحية الطلب. حاول مرة أخرى.';
        return $errors;
    }

    $email = trim((string) ($post['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'يرجى إدخال بريد إلكتروني صحيح.';
        return $errors;
    }

    // Check if email exists
    $stmt = db()->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetchColumn()) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        // Remove old tokens
        $del = db()->prepare('DELETE FROM password_resets WHERE email = ?');
        $del->execute([$email]);

        // Insert new token
        $ins = db()->prepare('INSERT INTO password_resets (email, token) VALUES (?, ?)');
        $ins->execute([$email, $hashedToken]);

        // Send email
        $config = require __DIR__ . '/config.php';
        $resetLink = $config['app_url'] . '/Public/reset-password/index.php?token=' . $token . '&email=' . urlencode($email);

        if (!send_reset_email($email, $resetLink, $config)) {
            error_log('Password reset: failed to send email for ' . $email);
        }
    }

    set_flash('success', 'إذا كان البريد مسجلًا، فسيتم إرسال رابط إعادة التعيين.');
    return [];
}

function send_reset_email(string $email, string $resetLink, array $config): bool
{
    $autoloadPaths = [
        __DIR__ . '/../vendor/autoload.php',
    ];

    $autoloaded = false;
    foreach ($autoloadPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $autoloaded = true;
            break;
        }
    }

    if (!$autoloaded || !class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        error_log('Password reset: PHPMailer not installed. Run composer install in project root.');

        return false;
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) ($config['mail_host'] ?? '');
        $mail->SMTPAuth = true;
        $mail->Port = (int) ($config['mail_port'] ?? 587);
        $mail->Username = (string) ($config['mail_username'] ?? '');
        $mail->Password = (string) ($config['mail_password'] ?? '');

        $encryption = strtolower((string) ($config['mail_encryption'] ?? 'tls'));
        if ($encryption === 'tls') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->setFrom((string) ($config['mail_from'] ?? ''), (string) ($config['mail_from_name'] ?? ''));
        $mail->addAddress($email);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'إعادة تعيين كلمة المرور';
        $mail->Body = "
        <div dir='rtl' style='font-family: Cairo, sans-serif;'>
            <h2>إعادة تعيين كلمة المرور</h2>
            <p>لقد طلبت إعادة تعيين كلمة المرور. اضغط على الرابط أدناه:</p>
            <a href='{$resetLink}' style='display:inline-block; padding:10px 20px; background:#36A397; color:#fff; text-decoration:none; border-radius:5px;'>إعادة تعيين كلمة المرور</a>
            <p style='margin-top:15px; color:#888;'>إذا لم تطلب ذلك، تجاهل هذا البريد.</p>
        </div>
    ";

        $mail->send();

        return true;
    } catch (\Throwable $e) {
        error_log('Password reset mail error: ' . $e->getMessage());

        return false;
    }
}

// ─── Reset Password

function validate_reset_token(string $token, string $email): bool
{
    $hashedToken = hash('sha256', $token);
    $stmt = db()->prepare(
        'SELECT email FROM password_resets
         WHERE email = ? AND token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
    );
    $stmt->execute([$email, $hashedToken]);
    return (bool) $stmt->fetchColumn();
}

function process_reset_password_request(array $post): array
{
    $errors = [];

    if (!verify_csrf($post['csrf_token'] ?? null)) {
        return ['انتهت صلاحية الطلب. أعد المحاولة.'];
    }

    $token = trim((string) ($post['token'] ?? ''));
    $email = trim((string) ($post['email'] ?? ''));
    $password = (string) ($post['password'] ?? '');
    $confirmPassword = (string) ($post['confirm_password'] ?? '');

    if ($token === '' || $email === '') {
        return ['رابط غير صالح.'];
    }

    if (mb_strlen($password) < 8) {
        return ['كلمة المرور يجب أن تكون 8 أحرف على الأقل.'];
    }

    if ($password !== $confirmPassword) {
        return ['كلمات المرور غير متطابقة.'];
    }

    if (!validate_reset_token($token, $email)) {
        return ['الرابط غير صالح أو منتهي الصلاحية.'];
    }

    // Update password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->execute([$hashed, $email]);

    // Delete used tokens
    $del = db()->prepare('DELETE FROM password_resets WHERE email = ?');
    $del->execute([$email]);

    set_flash('success', 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');
    return [];
}
