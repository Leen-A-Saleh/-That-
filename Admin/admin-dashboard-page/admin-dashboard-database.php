<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';

function get_dashboard_stats(): array
{
    $pdo = db();

    $activeUsers = (int) $pdo
        ->query("SELECT COUNT(*) FROM users WHERE role = 'CLIENT' AND is_active = 1")
        ->fetchColumn();

    $therapists = (int) $pdo
        ->query("SELECT COUNT(*) FROM users WHERE role = 'THERAPIST' AND is_active = 1")
        ->fetchColumn();

    $sessions = (int) $pdo->query("
        SELECT COUNT(*)
        FROM   appointments
        WHERE  status = 'COMPLETED'
          AND  MONTH(date_time) = MONTH(NOW())
          AND  YEAR(date_time) = YEAR(NOW())
    ")->fetchColumn();

    $totalResults = (int) $pdo
        ->query("SELECT COUNT(*) FROM assessment_results")
        ->fetchColumn();

    $canceledResults = (int) $pdo
        ->query("
        SELECT COUNT(*)
        FROM assessment_results
        WHERE UPPER(status) = 'CANCELED'
    ")
        ->fetchColumn();

    $nonCanceledResults = $totalResults - $canceledResults;

    $testCompletion = $totalResults > 0
        ? (int) round(($nonCanceledResults * 100) / $totalResults)
        : 0;

    $rating = (float) ($pdo->query(
        "SELECT ROUND(AVG(rating), 1) FROM therapists WHERE rating > 0"
    )->fetchColumn() ?? 0.0);

    return [
        'activeUsers' => $activeUsers,
        'activeUsersGrowth' => get_active_users_growth(),

        'therapists' => $therapists,
        'therapistsGrowth' => get_therapists_growth(),

        'sessions' => $sessions,
        'sessionsGrowth' => get_sessions_growth(),

        'tests' => $testCompletion,
        'testsGrowth' => get_tests_growth(),

        'revenue' => 0,

        'rating' => $rating,
        'ratingGrowth' => get_rating_value(),
    ];
}

// ============================================================
//  WEEKLY SESSIONS CHART 
// ============================================================

function get_weekly_sessions(): array
{
    $pdo = db();

    $arabicDays = [
        1 => 'الأحد',
        2 => 'الاثنين',
        3 => 'الثلاثاء',
        4 => 'الأربعاء',
        5 => 'الخميس',
        6 => 'الجمعة',
        7 => 'السبت',
    ];

    $rows = $pdo->query("
        SELECT DAYOFWEEK(start_time) AS dow, COUNT(*) AS cnt
        FROM   sessions
        WHERE  start_time >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP  BY dow
        ORDER  BY dow
    ")->fetchAll();

    $map = array_fill(1, 7, 0);

    foreach ($rows as $row) {
        $map[(int) $row['dow']] = (int) $row['cnt'];
    }

    $labels = [];
    $data   = [];

    foreach ($map as $dow => $cnt) {
        $labels[] = $arabicDays[$dow];
        $data[]   = $cnt;
    }

    return ['labels' => $labels, 'data' => $data];
}

// ============================================================
//  MONTHLY GROWTH CHART
// ============================================================

function get_monthly_growth(): array
{
    $pdo = db();

    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $months[] = date('Y-m', strtotime("-$i months"));
    }

    $userRows = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
        FROM   users
        WHERE  role = 'CLIENT'
          AND  created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP  BY ym
        ORDER  BY ym
    ")->fetchAll();

    $sessionRows = $pdo->query("
        SELECT DATE_FORMAT(start_time, '%Y-%m') AS ym, COUNT(*) AS cnt
        FROM   sessions
        WHERE  start_time >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP  BY ym
        ORDER  BY ym
    ")->fetchAll();

    $userMap    = array_column($userRows, 'cnt', 'ym');
    $sessionMap = array_column($sessionRows, 'cnt', 'ym');

    $arabicMonths = [
        '01' => 'يناير',
        '02' => 'فبراير',
        '03' => 'مارس',
        '04' => 'أبريل',
        '05' => 'مايو',
        '06' => 'يونيو',
        '07' => 'يوليو',
        '08' => 'أغسطس',
        '09' => 'سبتمبر',
        '10' => 'أكتوبر',
        '11' => 'نوفمبر',
        '12' => 'ديسمبر',
    ];

    $labels   = [];
    $users    = [];
    $sessions = [];

    foreach ($months as $ym) {
        [, $m] = explode('-', $ym);

        $labels[]   = $arabicMonths[$m];
        $users[]    = (int) ($userMap[$ym] ?? 0);
        $sessions[] = (int) ($sessionMap[$ym] ?? 0);
    }

    return [
        'labels'   => $labels,
        'users'    => $users,
        'sessions' => $sessions
    ];
}

// ============================================================
//  RECENT ACTIVITIES
// ============================================================

function get_recent_activities(): array
{
    $pdo = db();

    $sql = "
        (
            SELECT CONCAT('انضم مستخدم جديد: ', name) AS title, created_at
            FROM users
            WHERE role = 'CLIENT'
        )
        UNION ALL
        (
            SELECT CONCAT('تم إكمال جلسة مع ', u.name) AS title, s.start_time AS created_at
            FROM sessions s
            JOIN users u ON u.user_id = s.case_id
        )
        UNION ALL
        (
            SELECT CONCAT('انضم أخصائي جديد: ', name) AS title, created_at
            FROM users
            WHERE role = 'THERAPIST'
        )
        UNION ALL
        (
            SELECT 'أكمل مريض اختباراً نفسياً' AS title, created_at
            FROM assessment_results
        )
        ORDER BY created_at DESC
        LIMIT 5
    ";

    $rows = $pdo->query($sql)->fetchAll();

    $activities = [];

    foreach ($rows as $row) {
        $activities[] = [
            'title' => $row['title'],
            'time'  => time_ago_arabic($row['created_at']),
        ];
    }

    return $activities;
}

// ============================================================
//  TOP SPECIALISTS
// ============================================================

function get_top_specialists(): array
{
    $pdo = db();

    $rows = $pdo->query("
        SELECT   u.name,
                 t.rating,
                 COUNT(a.appointment_id) AS session_count
        FROM     therapists t
        JOIN     users u ON u.user_id = t.therapist_id
        LEFT JOIN appointments a
               ON a.therapist_id = t.therapist_id
              AND a.status = 'COMPLETED'
        GROUP BY t.therapist_id, u.name, t.rating
        ORDER BY session_count DESC, t.rating DESC
        LIMIT    5
    ")->fetchAll();

    $specialists = [];
    foreach ($rows as $row) {
        $specialists[] = [
            'name'     => $row['name'],
            'sessions' => (int) $row['session_count'],
            'rating'   => number_format((float) $row['rating'], 1),
        ];
    }

    return $specialists;
}

// ============================================================
//  HELPER — Arabic relative time
// ============================================================

function time_ago_arabic(string $datetime): string
{
    $diff = time() - (int) strtotime($datetime);

    if ($diff < 60)      return 'منذ لحظات';
    if ($diff < 120)     return 'منذ دقيقة';
    if ($diff < 3600)    return 'منذ ' . (int) ($diff / 60)   . ' دقائق';
    if ($diff < 7200)    return 'منذ ساعة';
    if ($diff < 86400)   return 'منذ ' . (int) ($diff / 3600)  . ' ساعات';
    if ($diff < 172800)  return 'منذ يومين';
    if ($diff < 2592000) return 'منذ ' . (int) ($diff / 86400) . ' أيام';
    return 'منذ ' . (int) ($diff / 2592000) . ' أشهر';
}

function get_active_users_growth(): float
{
    $pdo = db();

    $current = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM users 
        WHERE role = 'CLIENT'
          AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    $previous = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM users 
        WHERE role = 'CLIENT'
          AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY)
          AND DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    return $previous > 0
        ? round((($current - $previous) / $previous) * 100, 1)
        : 0;
}

function get_therapists_growth(): float
{
    $pdo = db();

    $current = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM users 
        WHERE role = 'THERAPIST'
          AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    $previous = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM users 
        WHERE role = 'THERAPIST'
          AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY)
          AND DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    return $previous > 0
        ? round((($current - $previous) / $previous) * 100, 1)
        : 0;
}

function get_sessions_growth(): float
{
    $pdo = db();

    $current = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM sessions 
        WHERE start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    $previous = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM sessions 
        WHERE start_time BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY)
        AND DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    return $previous > 0
        ? round((($current - $previous) / $previous) * 100, 1)
        : 0;
}

function get_tests_growth(): float
{
    $pdo = db();

    $current = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM assessment_results 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    $previous = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM assessment_results 
        WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY)
        AND DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetchColumn();

    return $previous > 0
        ? round((($current - $previous) / $previous) * 100, 1)
        : 0;
}

function get_rating_value(): float
{
    $pdo = db();

    $rating = (float) $pdo->query("
        SELECT AVG(rating)
        FROM therapists
    ")->fetchColumn();

    return round($rating, 1);
}
