<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/db.php';
require_once __DIR__ . '/../../../Database/client.php';
require_once __DIR__ . '/../game-results.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$clientId = crossword_current_client_id();
if ($clientId <= 0) {
    crossword_json(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

try {
    if ($action === 'save-level' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        crossword_save_level($clientId);
    }

    if ($action === 'history' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        crossword_history($clientId);
    }

    crossword_json(['success' => false, 'message' => 'Invalid request.'], 400);
} catch (Throwable) {
    crossword_json(['success' => false, 'message' => 'Server error.'], 500);
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

function crossword_current_client_id(): int
{
    $user = current_user();
    if (!is_array($user)) {
        return 0;
    }

    if (strtoupper((string) ($user['role'] ?? '')) !== 'CLIENT') {
        crossword_json(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    return (int) ($user['user_id'] ?? 0);
}

// ─── Actions ──────────────────────────────────────────────────────────────────

function crossword_save_level(int $clientId): void
{
    $payload = crossword_payload();
    $level = (int) ($payload['level'] ?? 0);
    $levelsPassed = (int) ($payload['levels_passed'] ?? 0);
    $totalTime = (int) ($payload['time_seconds'] ?? 0);
    $isCompleted = !empty($payload['is_completed']) ? 1 : 0;
    $timePerLevel = $payload['time_per_level'] ?? null;

    if ($level < 1 || $level > 5 || $levelsPassed < 1 || $levelsPassed > 5 || $levelsPassed !== $level || $totalTime < 0 || $totalTime > 65535 || !is_array($timePerLevel)) {
        crossword_json(['success' => false, 'message' => 'Invalid progress data.'], 422);
    }

    if ($isCompleted === 1 && $level !== 5) {
        crossword_json(['success' => false, 'message' => 'Only level 5 can complete the game.'], 422);
    }

    $cleanTimes = [];
    for ($i = 1; $i <= 5; $i++) {
        $key = 'level' . $i;
        $seconds = (int) ($timePerLevel[$key] ?? 0);
        if ($seconds < 0 || $seconds > 600) {
            crossword_json(['success' => false, 'message' => 'Invalid level time.'], 422);
        }
        $cleanTimes[$key] = $seconds;
    }

    $timeJson = json_encode($cleanTimes, JSON_UNESCAPED_UNICODE);
    if (!is_string($timeJson)) {
        crossword_json(['success' => false, 'message' => 'Could not encode progress.'], 422);
    }

    $result = game_result_save_to_pending_or_insert($clientId, 'Crossword', [
        'is_completed' => $isCompleted,
        'time_seconds' => $totalTime,
        'points' => null,
        'moves' => null,
        'difficulty' => null,
        'level' => $level,
        'time_per_level' => $timeJson,
        'levels_passed' => (string) $levelsPassed,
    ]);

    crossword_json([
        'success' => true,
        'result_id' => $result['id'],
    ]);
}

function crossword_history(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT id, played_at, level, time_seconds, levels_passed, is_completed
         FROM game_results
         WHERE client_id = :client_id AND game_name = :game_name
         ORDER BY played_at DESC, id DESC
         LIMIT 50'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Crossword',
    ]);

    crossword_json([
        'success' => true,
        'sessions' => $stmt->fetchAll(),
    ]);
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * @return array<string,mixed>
 */
function crossword_payload(): array
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
 * @param array<string,mixed> $data
 */
function crossword_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
