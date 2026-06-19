<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/db.php';
require_once __DIR__ . '/../../../Database/client.php';
require_once __DIR__ . '/../game-results.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$clientId = difference_current_client_id();
if ($clientId <= 0) {
    difference_json(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

try {
    if ($action === 'save' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        difference_save_result($clientId);
    }

    if ($action === 'history' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        difference_history($clientId);
    }

    if ($action === 'count' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        difference_count($clientId);
    }

    difference_json(['success' => false, 'message' => 'Invalid request.'], 400);
} catch (Throwable) {
    difference_json(['success' => false, 'message' => 'Server error.'], 500);
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

function difference_current_client_id(): int
{
    $user = current_user();
    if (!is_array($user)) {
        return 0;
    }

    if (strtoupper((string) ($user['role'] ?? '')) !== 'CLIENT') {
        difference_json(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    return (int) ($user['user_id'] ?? 0);
}

// ─── Actions ──────────────────────────────────────────────────────────────────

function difference_save_result(int $clientId): void
{
    $payload = difference_payload();

    $totalLevels = (int) ($payload['level'] ?? 0);
    $isCompleted = !empty($payload['is_completed']) ? 1 : 0;
    $timePerLevel = difference_assoc_array($payload['time_per_level'] ?? null);
    $levelsPassed = difference_assoc_array($payload['levels_passed'] ?? null);

    if ($totalLevels !== 2 || $timePerLevel === [] || $levelsPassed === []) {
        difference_json(['success' => false, 'message' => 'Invalid result data.'], 422);
    }

    $totalTime = 0;
    foreach ($timePerLevel as $level => $seconds) {
        if (!preg_match('/^[1-2]$/', (string) $level)) {
            difference_json(['success' => false, 'message' => 'Invalid level data.'], 422);
        }

        $seconds = (int) $seconds;
        if ($seconds < 0 || $seconds > 120) {
            difference_json(['success' => false, 'message' => 'Invalid level time.'], 422);
        }

        $timePerLevel[(string) $level] = $seconds;
        $totalTime += $seconds;
    }

    foreach ($levelsPassed as $level => $status) {
        if (!preg_match('/^[1-2]$/', (string) $level) || !in_array($status, ['pass', 'fail'], true)) {
            difference_json(['success' => false, 'message' => 'Invalid level status.'], 422);
        }
    }

    if ($isCompleted === 1 && (($levelsPassed['1'] ?? null) !== 'pass' || ($levelsPassed['2'] ?? null) !== 'pass')) {
        difference_json(['success' => false, 'message' => 'Completed result must pass all levels.'], 422);
    }

    $timeJson = json_encode($timePerLevel, JSON_UNESCAPED_UNICODE);
    $passedJson = json_encode($levelsPassed, JSON_UNESCAPED_UNICODE);
    if (!is_string($timeJson) || !is_string($passedJson)) {
        difference_json(['success' => false, 'message' => 'Could not encode result.'], 422);
    }

    $result = game_result_save_to_pending_or_insert($clientId, 'Difference', [
        'is_completed' => $isCompleted,
        'time_seconds' => $totalTime,
        'points' => null,
        'moves' => null,
        'difficulty' => null,
        'level' => $totalLevels,
        'time_per_level' => $timeJson,
        'levels_passed' => $passedJson,
    ]);

    difference_json([
        'success' => true,
        'result_id' => $result['id'],
    ]);
}

function difference_history(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT id, played_at, is_completed, time_seconds, level, time_per_level, levels_passed
         FROM game_results
         WHERE client_id = :client_id AND game_name = :game_name
         ORDER BY played_at DESC, id DESC
         LIMIT 50'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Difference',
    ]);

    difference_json([
        'success' => true,
        'sessions' => $stmt->fetchAll(),
    ]);
}

function difference_count(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT COUNT(*)
         FROM game_results
         WHERE client_id = :client_id AND game_name = :game_name'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Difference',
    ]);

    difference_json([
        'success' => true,
        'count' => (int) $stmt->fetchColumn(),
    ]);
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * @return array<string,mixed>
 */
function difference_payload(): array
{
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($contentType, 'application/json')) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode(is_string($raw) ? $raw : '', true);

        return is_array($decoded) ? $decoded : [];
    }

    return $_POST;
}

/**
 * @return array<string,mixed>
 */
function difference_assoc_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

/**
 * @param array<string,mixed> $data
 */
function difference_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
