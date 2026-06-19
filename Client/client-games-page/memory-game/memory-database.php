<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/db.php';
require_once __DIR__ . '/../../../Database/client.php';
require_once __DIR__ . '/../game-results.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$clientId = memory_current_client_id();
if ($clientId <= 0) {
    memory_json(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

try {
    if ($action === 'save' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        memory_save_result($clientId);
    }

    if ($action === 'history' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        memory_history($clientId);
    }

    if ($action === 'count' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        memory_count($clientId);
    }

    memory_json(['success' => false, 'message' => 'Invalid request.'], 400);
} catch (Throwable) {
    memory_json(['success' => false, 'message' => 'Server error.'], 500);
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

function memory_current_client_id(): int
{
    $user = current_user();
    if (!is_array($user)) {
        return 0;
    }

    if (strtoupper((string) ($user['role'] ?? '')) !== 'CLIENT') {
        memory_json(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    return (int) ($user['user_id'] ?? 0);
}

// ─── Actions ──────────────────────────────────────────────────────────────────

function memory_save_result(int $clientId): void
{
    $payload = memory_payload();
    $timeSeconds = (int) ($payload['time_seconds'] ?? 0);
    $moves = (int) ($payload['moves'] ?? 0);
    $difficulty = strtoupper(trim((string) ($payload['difficulty'] ?? '')));

    if ($timeSeconds < 0 || $timeSeconds > 65535 || $moves < 1 || $moves > 65535 || !in_array($difficulty, ['EASY', 'MEDIUM'], true)) {
        memory_json(['success' => false, 'message' => 'Invalid result data.'], 422);
    }

    $result = game_result_save_to_pending_or_insert($clientId, 'Cards', [
        'is_completed' => 1,
        'time_seconds' => $timeSeconds,
        'points' => null,
        'moves' => $moves,
        'difficulty' => $difficulty,
        'level' => null,
        'time_per_level' => null,
        'levels_passed' => null,
    ]);

    memory_json([
        'success' => true,
        'result_id' => $result['id'],
    ]);
}

function memory_history(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT id, played_at, time_seconds, moves, difficulty
         FROM game_results
         WHERE client_id = :client_id
           AND game_name = :game_name
           AND is_completed = 1
         ORDER BY played_at DESC, id DESC
         LIMIT 50'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Cards',
    ]);

    memory_json([
        'success' => true,
        'sessions' => $stmt->fetchAll(),
    ]);
}

function memory_count(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT COUNT(*)
         FROM game_results
         WHERE client_id = :client_id
           AND game_name = :game_name
           AND is_completed = 1'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Cards',
    ]);

    memory_json([
        'success' => true,
        'count' => (int) $stmt->fetchColumn(),
    ]);
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * @return array<string,mixed>
 */
function memory_payload(): array
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
function memory_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
