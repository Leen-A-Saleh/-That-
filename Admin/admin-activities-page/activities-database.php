<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';

function activities_dbTypeToUi(string $dbType): string
{
    return match (strtoupper($dbType)) {
        'GAME'     => 'تفاعل',
        'EXERCISE' => 'تمرين',
        'TASK'     => 'مهمة',
        default    => 'تفاعل',
    };
}

function activities_uiTypeToDb(string $uiType): string
{
    return match ($uiType) {
        'تفاعل' => 'GAME',
        'تمرين' => 'EXERCISE',
        'مهمة'  => 'TASK',
        default => 'GAME',
    };
}

function activities_dbStatusToJs(string $dbStatus): string
{
    return strtolower($dbStatus) === 'active' ? 'active' : 'inactive';
}

function activities_jsStatusToDb(string $jsStatus): string
{
    return $jsStatus === 'active' ? 'ACTIVE' : 'DRAFT';
}


// ─── Stats ────────────────────────────────────────────────────────────────────

function activities_getStats(): array
{
    $total = (int) db()->query("
        SELECT COUNT(*) FROM activities
    ")->fetchColumn();

    $views = (int) db()->query("
        SELECT COALESCE(SUM(views), 0) FROM activities
    ")->fetchColumn();

    $gamePlays = (int) db()->query("
        SELECT COUNT(*) FROM game_results WHERE is_completed = 1
    ")->fetchColumn();

    $active = (int) db()->query("
        SELECT COUNT(*) FROM activities WHERE status = 'ACTIVE'
    ")->fetchColumn();

    return [
        'total'     => $total,
        'views'     => $views,
        'gamePlays' => $gamePlays,
        'active'    => $active,
    ];
}


// ─── Fetch All ───────────────────────────────────────────────────────────────

function activities_getAll(): array
{
    $rows = db()->query("
        SELECT
            activity_id,
            title,
            COALESCE(description, '')  AS description,
            COALESCE(category, '')     AS category,
            activity_type,
            COALESCE(duration_min, '')  AS duration_min,
            status,
            COALESCE(views, 0)         AS views,
            COALESCE(game_key, '')     AS game_key,
            COALESCE(link, '')         AS link,
            created_at
        FROM activities
        ORDER BY activity_id DESC
    ")->fetchAll();

    return array_map(function (array $r): array {
        return [
            'id'            => (int) $r['activity_id'],
            'title'         => $r['title'],
            'description'   => $r['description'],
            'cat'           => $r['category'],
            'type'          => activities_dbTypeToUi($r['activity_type']),
            'activity_type' => strtoupper($r['activity_type']),
            'duration'      => $r['duration_min'],
            'status'        => activities_dbStatusToJs($r['status']),
            'views'         => (int) $r['views'],
            'downloads'     => 0,   // column does not exist yet — hardcoded per spec
            'game_key'      => $r['game_key'],
            'link'          => $r['link'],
            'date'          => $r['created_at']
                ? date('d-m-Y', strtotime($r['created_at']))
                : '',
        ];
    }, $rows);
}


// ─── Create ───────────────────────────────────────────────────────────────────

function activities_add(
    string $title,
    string $description,
    string $category,
    string $activityType,   // UI label: تفاعل / تمرين / مهمة
    string $durationMin,
    string $status           // JS value: active / inactive
): int {
    if ($title === '') {
        throw new RuntimeException('اسم النشاط مطلوب');
    }

    $dbType   = activities_uiTypeToDb($activityType);
    $dbStatus = activities_jsStatusToDb($status);

    $stmt = db()->prepare("
        INSERT INTO activities
            (title, description, category, activity_type, duration_min, status, views, created_by)
        VALUES
            (:title, :description, :category, :activity_type, :duration_min, :status, 0, 1)
    ");

    $stmt->execute([
        ':title'         => $title,
        ':description'   => $description,
        ':category'      => $category,
        ':activity_type' => $dbType,
        ':duration_min'  => $durationMin,
        ':status'        => $dbStatus,
    ]);

    return (int) db()->lastInsertId();
}


// ─── Update ───────────────────────────────────────────────────────────────────

function activities_update(
    int    $id,
    string $title,
    string $description,
    string $category,
    string $activityType,   // UI label
    string $durationMin,
    string $status           // JS value
): void {
    if ($id <= 0) {
        throw new RuntimeException('معرّف النشاط غير صالح');
    }

    // Verify the record exists first
    $check = db()->prepare("SELECT activity_id FROM activities WHERE activity_id = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        throw new RuntimeException('النشاط غير موجود');
    }

    $dbType   = activities_uiTypeToDb($activityType);
    $dbStatus = activities_jsStatusToDb($status);

    db()->prepare("
        UPDATE activities
        SET
            title         = :title,
            description   = :description,
            category      = :category,
            activity_type = :activity_type,
            duration_min  = :duration_min,
            status        = :status
        WHERE activity_id = :id
    ")->execute([
        ':title'         => $title,
        ':description'   => $description,
        ':category'      => $category,
        ':activity_type' => $dbType,
        ':duration_min'  => $durationMin,
        ':status'        => $dbStatus,
        ':id'            => $id,
    ]);
}


// ─── Toggle Status ────────────────────────────────────────────────────────────

function activities_toggleStatus(int $id, string $jsStatus): void
{
    if ($id <= 0) {
        throw new RuntimeException('معرّف النشاط غير صالح');
    }

    $dbStatus = activities_jsStatusToDb($jsStatus);

    db()->prepare("
        UPDATE activities SET status = :status WHERE activity_id = :id
    ")->execute([':status' => $dbStatus, ':id' => $id]);
}


// ─── Fetch Categories ─────────────────────────────────────────────────────────

function activities_getCategories(): array
{
    $rows = db()->query("
        SELECT DISTINCT category
        FROM activities
        WHERE category IS NOT NULL AND category != ''
        ORDER BY category ASC
    ")->fetchAll(PDO::FETCH_COLUMN);

    return array_values($rows);
}


// ─── Delete ───────────────────────────────────────────────────────────────────

function activities_delete(int $id): void
{
    if ($id <= 0) {
        throw new RuntimeException('معرّف النشاط غير صالح');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $check = $pdo->prepare("SELECT activity_id FROM activities WHERE activity_id = :id");
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            throw new RuntimeException('النشاط غير موجود');
        }

        $pdo->prepare("
            DELETE FROM activities WHERE activity_id = :id
        ")->execute([':id' => $id]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
