<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/db.php';
require_once __DIR__ . '/../../../Database/client.php';
require_once __DIR__ . '/../game-results.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$clientId = questions_current_client_id();
if ($clientId <= 0) {
    questions_json(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

try {
    if ($action === 'start' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        questions_start_session($clientId);
    }

    if ($action === 'update' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        questions_update_session($clientId);
    }

    if ($action === 'complete' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        questions_complete_session($clientId);
    }

    if ($action === 'history' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        questions_history($clientId);
    }

    if ($action === 'count' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        questions_count($clientId);
    }

    questions_json(['success' => false, 'message' => 'Invalid request.'], 400);
} catch (Throwable) {
    questions_json(['success' => false, 'message' => 'Server error.'], 500);
}

// ─── Auth ────────────────────────────────────────────────────────────────────

function questions_current_client_id(): int
{
    $user = current_user();
    if (!is_array($user)) {
        return 0;
    }

    if (strtoupper((string) ($user['role'] ?? '')) !== 'CLIENT') {
        questions_json(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    return (int) ($user['user_id'] ?? 0);
}

// ─── Actions ─────────────────────────────────────────────────────────────────

function questions_start_session(int $clientId): void
{
    $payload = questions_payload();

    $totalLevels = (int) ($payload['total_levels'] ?? 2);
    if ($totalLevels < 1 || $totalLevels > 10) {
        questions_json(['success' => false, 'message' => 'Invalid total_levels.'], 422);
    }

    $levelsPassed = [];
    for ($i = 1; $i <= $totalLevels; $i++) {
        $levelsPassed['level' . $i] = false;
    }

    $levelsPassedJson = json_encode($levelsPassed, JSON_UNESCAPED_UNICODE);
    if (!is_string($levelsPassedJson)) {
        questions_json(['success' => false, 'message' => 'Could not encode session data.'], 422);
    }

    $result = game_result_save_to_pending_or_insert($clientId, 'Questions', [
        'is_completed' => 0,
        'time_seconds' => null,
        'points' => 0,
        'moves' => null,
        'difficulty' => null,
        'level' => 1,
        'time_per_level' => null,
        'levels_passed' => $levelsPassedJson,
    ]);

    questions_json([
        'success'    => true,
        'session_id' => $result['id'],
    ]);
}

function questions_update_session(int $clientId): void
{
    $payload = questions_payload();

    $sessionId    = (int) ($payload['session_id'] ?? 0);
    $level        = (int) ($payload['level'] ?? 0);
    $points       = (int) ($payload['points'] ?? 0);
    $levelsPassed = questions_assoc_array($payload['levels_passed'] ?? null);

    if ($sessionId <= 0 || $level < 1 || $points < 0 || $levelsPassed === []) {
        questions_json(['success' => false, 'message' => 'Invalid update data.'], 422);
    }

    // Validate levels_passed values: must all be booleans
    foreach ($levelsPassed as $key => $value) {
        if (!preg_match('/^level\d+$/', (string) $key) || !is_bool($value)) {
            questions_json(['success' => false, 'message' => 'Invalid levels_passed data.'], 422);
        }
    }

    $levelsPassedJson = json_encode($levelsPassed, JSON_UNESCAPED_UNICODE);
    if (!is_string($levelsPassedJson)) {
        questions_json(['success' => false, 'message' => 'Could not encode levels_passed.'], 422);
    }

    // Verify the session belongs to this client before updating
    $check = db()->prepare(
        'SELECT id
         FROM game_results
         WHERE id = :id
           AND client_id = :client_id
           AND game_name = :game_name
           AND is_completed = 0'
    );
    $check->execute([
        'id'        => $sessionId,
        'client_id' => $clientId,
        'game_name' => 'Questions',
    ]);

    if (!$check->fetch()) {
        questions_json(['success' => false, 'message' => 'Session not found.'], 404);
    }

    $stmt = db()->prepare(
        'UPDATE game_results
         SET level = :level, points = :points, levels_passed = :levels_passed
         WHERE id = :id'
    );
    $stmt->execute([
        'level'         => $level,
        'points'        => $points,
        'levels_passed' => $levelsPassedJson,
        'id'            => $sessionId,
    ]);

    questions_json(['success' => true]);
}

function questions_complete_session(int $clientId): void
{
    $payload   = questions_payload();
    $sessionId = (int) ($payload['session_id'] ?? 0);

    if ($sessionId <= 0) {
        questions_json(['success' => false, 'message' => 'Invalid session_id.'], 422);
    }

    $check = db()->prepare(
        'SELECT id, levels_passed
         FROM game_results
         WHERE id = :id
           AND client_id = :client_id
           AND game_name = :game_name
           AND is_completed = 0'
    );
    $check->execute([
        'id'        => $sessionId,
        'client_id' => $clientId,
        'game_name' => 'Questions',
    ]);

    $session = $check->fetch();
    if (!$session) {
        questions_json(['success' => false, 'message' => 'Session not found.'], 404);
    }

    $levelsPassed = json_decode((string) ($session['levels_passed'] ?? ''), true);
    if (!is_array($levelsPassed) || $levelsPassed === []) {
        questions_json(['success' => false, 'message' => 'Progress is incomplete.'], 422);
    }

    foreach ($levelsPassed as $passed) {
        if ($passed !== true) {
            questions_json(['success' => false, 'message' => 'Progress is incomplete.'], 422);
        }
    }

    $stmt = db()->prepare(
        'UPDATE game_results
         SET is_completed = 1
         WHERE id = :id AND client_id = :client_id AND game_name = :game_name AND is_completed = 0'
    );
    $stmt->execute([
        'id' => $sessionId,
        'client_id' => $clientId,
        'game_name' => 'Questions',
    ]);

    questions_json(['success' => true]);
}

function questions_history(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT id, played_at, is_completed, points, level, levels_passed
         FROM game_results
         WHERE client_id = :client_id AND game_name = :game_name
         ORDER BY played_at DESC, id DESC
         LIMIT 50'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Questions',
    ]);

    questions_json([
        'success'  => true,
        'sessions' => $stmt->fetchAll(),
    ]);
}

function questions_count(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT COUNT(*)
         FROM game_results
         WHERE client_id = :client_id AND game_name = :game_name AND is_completed = 1'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Questions',
    ]);

    questions_json([
        'success' => true,
        'count'   => (int) $stmt->fetchColumn(),
    ]);
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * @return array<string,mixed>
 */
function questions_payload(): array
{
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($contentType, 'application/json')) {
        $raw     = file_get_contents('php://input');
        $decoded = json_decode(is_string($raw) ? $raw : '', true);

        return is_array($decoded) ? $decoded : [];
    }

    return $_POST;
}

/**
 * @return array<string,mixed>
 */
function questions_assoc_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

/**
 * @param array<string,mixed> $data
 */
function questions_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
