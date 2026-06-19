<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/db.php';
require_once __DIR__ . '/../../../Database/client.php';
require_once __DIR__ . '/../game-results.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$clientId = breath_current_client_id();
if ($clientId <= 0) {
    breath_json(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

try {
    if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        breath_create_session($clientId);
    }

    if ($action === 'complete' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        breath_complete_session($clientId);
    }

    if ($action === 'history' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        breath_history($clientId);
    }

    if ($action === 'count' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        breath_count($clientId);
    }

    if ($action === 'delete' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        breath_delete_history($clientId);
    }

    breath_json(['success' => false, 'message' => 'Invalid request.'], 400);
} catch (Throwable) {
    breath_json(['success' => false, 'message' => 'Server error.'], 500);
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

function breath_current_client_id(): int
{
    $user = current_user();
    if (!is_array($user)) {
        return 0;
    }

    if (strtoupper((string) ($user['role'] ?? '')) !== 'CLIENT') {
        breath_json(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    return (int) ($user['user_id'] ?? 0);
}

// ─── Actions ──────────────────────────────────────────────────────────────────

function breath_create_session(int $clientId): void
{
    $result = game_result_start_or_touch_pending($clientId, 'Breathing');

    breath_json([
        'success' => true,
        'session_id' => $result['id'],
    ]);
}

function breath_complete_session(int $clientId): void
{
    $payload = breath_payload();
    $sessionId = (int) ($payload['session_id'] ?? 0);
    $duration = (int) ($payload['duration'] ?? 0);

    if ($sessionId <= 0 || $duration < 1) {
        breath_json(['success' => false, 'message' => 'Invalid session data.'], 422);
    }

    $check = db()->prepare(
        'SELECT id
         FROM game_results
         WHERE id = :id AND client_id = :client_id AND game_name = :game_name
         LIMIT 1'
    );
    $check->execute([
        'id' => $sessionId,
        'client_id' => $clientId,
        'game_name' => 'Breathing',
    ]);

    if (!$check->fetchColumn()) {
        breath_json(['success' => false, 'message' => 'Session not found.'], 404);
    }

    $stmt = db()->prepare(
        'UPDATE game_results
         SET is_completed = 1, time_seconds = :duration
         WHERE id = :id AND client_id = :client_id AND game_name = :game_name'
    );
    $stmt->execute([
        'duration' => $duration,
        'id' => $sessionId,
        'client_id' => $clientId,
        'game_name' => 'Breathing',
    ]);

    breath_json(['success' => true]);
}

function breath_history(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT id, played_at, time_seconds
         FROM game_results
         WHERE client_id = :client_id
           AND game_name = :game_name
           AND is_completed = 1
         ORDER BY played_at DESC, id DESC
         LIMIT 50'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Breathing',
    ]);

    $sessions = [];
    foreach ($stmt->fetchAll() as $row) {
        $sessions[] = [
            'id' => (int) ($row['id'] ?? 0),
            'played_at' => (string) ($row['played_at'] ?? ''),
            'duration' => (int) ($row['time_seconds'] ?? 0),
        ];
    }

    breath_json([
        'success' => true,
        'sessions' => $sessions,
    ]);
}

function breath_count(int $clientId): void
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
        'game_name' => 'Breathing',
    ]);

    breath_json([
        'success' => true,
        'count' => (int) $stmt->fetchColumn(),
    ]);
}

function breath_delete_history(int $clientId): void
{
    $stmt = db()->prepare(
        'DELETE FROM game_results
         WHERE client_id = :client_id
           AND game_name = :game_name'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Breathing',
    ]);

    breath_json([
        'success' => true,
        'deleted' => $stmt->rowCount(),
    ]);
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * @return array<string,mixed>
 */
function breath_payload(): array
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
function breath_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
