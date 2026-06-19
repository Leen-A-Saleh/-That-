<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';

// ─── Stats ────────────────────────────────────────────────────────────────────

function tests_getStats(): array
{
    $totalTests = (int) db()->query("
        SELECT COUNT(*) FROM assessments
    ")->fetchColumn();

    $totalCompletions = (int) db()->query("
        SELECT COUNT(*) 
        FROM assessment_results
        WHERE status = 'COMPLETED'
    ")->fetchColumn();

    $thisMonth = (int) db()->query("
        SELECT COUNT(*) FROM assessment_results
        WHERE status = 'COMPLETED'
          AND YEAR(created_at)  = YEAR(NOW())
          AND MONTH(created_at) = MONTH(NOW())
    ")->fetchColumn();

    $firstResult = db()->query("
        SELECT MIN(created_at) FROM assessment_results
        WHERE status = 'COMPLETED'
    ")->fetchColumn();

    $dailyAvg = 0;
    if ($firstResult) {
        $days = max(1, (int) db()->query("
            SELECT DATEDIFF(NOW(), MIN(created_at))
            FROM assessment_results
            WHERE status = 'COMPLETED'
        ")->fetchColumn());
        $dailyAvg = $days > 0 ? round($totalCompletions / $days) : $totalCompletions;
    }

    return [
        'total_tests' => $totalTests,
        'completions' => $totalCompletions,
        'this_month'  => $thisMonth,
        'daily_avg'   => $dailyAvg,
    ];
}

// ─── Monthly ──────────────────────────────────────────────────────────────────

function tests_getMonthly(): array
{
    $rows = db()->query("
        SELECT
            YEAR(created_at)  AS yr,
            MONTH(created_at) AS mo,
            COUNT(*)          AS total
        FROM assessment_results
        WHERE status = 'COMPLETED'
          AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY yr, mo
        ORDER BY yr ASC, mo ASC
    ")->fetchAll();

    $arMonths = [
        1  => 'يناير',
        2  => 'فبراير',
        3  => 'مارس',
        4  => 'أبريل',
        5  => 'مايو',
        6  => 'يونيو',
        7  => 'يوليو',
        8  => 'أغسطس',
        9  => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    $labels = [];
    $data   = [];

    foreach ($rows as $row) {
        $labels[] = $arMonths[(int) $row['mo']];
        $data[]   = (int) $row['total'];
    }

    return ['labels' => $labels, 'data' => $data];
}

// ─── Category Bars ────────────────────────────────────────────────────────────

function tests_getCategoryBars(): array
{
    $rows = db()->query("
        SELECT
            a.category,
            COUNT(ar.result_id) AS total
        FROM assessments a
        LEFT JOIN assessment_results ar
            ON ar.assessment_id = a.assessment_id
           AND ar.status = 'COMPLETED'
        GROUP BY a.category
        ORDER BY total DESC
    ")->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $result[] = [
            'cat'   => $row['category'] ?? 'أخرى',
            'total' => (int) $row['total'],
        ];
    }
    return $result;
}

// ─── Fetch All ────────────────────────────────────────────────────────────────

function tests_getAll(): array
{
    $rows = db()->query("
        SELECT
            a.assessment_id AS id,
            a.title_ar AS nameAr,
            a.title AS nameEn,
            COALESCE(a.category, '') AS cat,
            COALESCE(a.question_count, 0) AS questions,
            COALESCE(a.description, '') AS description,
            a.is_active,
            COUNT(ar.result_id) AS completions,
            COALESCE(ROUND(AVG(ar.trait_score), 1), 0) AS avg_score
        FROM assessments a
        LEFT JOIN assessment_results ar
            ON ar.assessment_id = a.assessment_id
           AND ar.status = 'COMPLETED'
        GROUP BY
            a.assessment_id, a.title_ar, a.title,
            a.category, a.question_count, a.description, a.is_active
        ORDER BY a.assessment_id DESC
    ")->fetchAll();

    return array_map(function (array $r): array {
        return [
            'id'          => (int) $r['id'],
            'nameAr'      => $r['nameAr'] ?? '',
            'nameEn'      => $r['nameEn'] ?? '',
            'cat'         => $r['cat'],
            'questions'   => (int) $r['questions'],
            'description' => $r['description'],
            'status'      => ((int) $r['is_active'] === 1) ? 'active' : 'draft',
            'completions' => (int) $r['completions'],
            'avg'         => (float) $r['avg_score'],
        ];
    }, $rows);
}

// ─── Create ───────────────────────────────────────────────────────────────────

function tests_add(
    string $nameAr,
    string $nameEn,
    string $cat,
    int    $questions,
    string $status,
    string $description
): int {
    if ($nameAr === '' || $nameEn === '' || $questions <= 0) {
        throw new RuntimeException('الحقول المطلوبة غير مكتملة');
    }

    $code     = strtoupper(substr(preg_replace('/\s+/', '_', $nameEn), 0, 20)) . '_' . random_int(100, 999);
    $isActive = ($status === 'active') ? 1 : 0;

    $stmt = db()->prepare("
        INSERT INTO assessments
            (code, title, title_ar, category, question_count, description, is_active)
        VALUES
            (:code, :title, :title_ar, :category, :questions, :description, :is_active)
    ");
    $stmt->execute([
        ':code'        => $code,
        ':title'       => $nameEn,
        ':title_ar'    => $nameAr,
        ':category'    => $cat,
        ':questions'   => $questions,
        ':description' => $description,
        ':is_active'   => $isActive,
    ]);

    return (int) db()->lastInsertId();
}

// ─── Update ───────────────────────────────────────────────────────────────────

function tests_update(
    int    $id,
    string $nameAr,
    string $nameEn,
    string $cat,
    int    $questions,
    string $status,
    string $description
): void {
    if ($id <= 0) {
        throw new RuntimeException('البيانات المُدخلة غير صالحة');
    }

    $existing = db()->prepare("
        SELECT title_ar, title, category, question_count, description, is_active
        FROM assessments WHERE assessment_id = :id
    ");
    $existing->execute([':id' => $id]);
    $row = $existing->fetch();

    if (!$row) {
        throw new RuntimeException('الاختبار غير موجود');
    }

    $finalNameAr    = $nameAr      !== '' ? $nameAr      : $row['title_ar'];
    $finalNameEn    = $nameEn      !== '' ? $nameEn      : $row['title'];
    $finalCat       = $cat         !== '' ? $cat         : $row['category'];
    $finalQuestions = $questions   >  0   ? $questions   : (int) $row['question_count'];
    $finalDesc      = $description !== '' ? $description : $row['description'];

    db()->prepare("
        UPDATE assessments
        SET title_ar       = :title_ar,
            title          = :title,
            category       = :category,
            question_count = :questions,
            description    = :description,
            is_active      = :is_active
        WHERE assessment_id = :id
    ")->execute([
        ':title_ar'  => $finalNameAr,
        ':title'     => $finalNameEn,
        ':category'  => $finalCat,
        ':questions' => $finalQuestions,
        ':description' => $finalDesc,
        ':is_active' => ($status === 'active') ? 1 : 0,
        ':id'        => $id,
    ]);
}

// ─── Delete ───────────────────────────────────────────────────────────────────

function tests_delete(int $id): void
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $check = $pdo->prepare("SELECT assessment_id FROM assessments WHERE assessment_id = :id");
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            throw new RuntimeException('الاختبار غير موجود');
        }

        $pdo->prepare("
            DELETE FROM assessment_results WHERE assessment_id = :id
        ")->execute([':id' => $id]);

        $pdo->prepare("
            DELETE FROM assessments WHERE assessment_id = :id
        ")->execute([':id' => $id]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}