<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Database/helpers.php';
require_once __DIR__ . '/../../../Database/db.php';
require_once __DIR__ . '/../../../Database/client.php';
require_once __DIR__ . '/../game-results.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$clientId = misplacedpin_current_client_id();
if ($clientId <= 0) {
    misplacedpin_json(['success' => false, 'message' => 'Unauthorized.'], 401);
}

$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

try {
    if ($action === 'save' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        misplacedpin_save_result($clientId);
    }

    if ($action === 'history' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        misplacedpin_history($clientId);
    }

    if ($action === 'count' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        misplacedpin_count($clientId);
    }

    misplacedpin_json(['success' => false, 'message' => 'Invalid request.'], 400);
} catch (Throwable) {
    misplacedpin_json(['success' => false, 'message' => 'Server error.'], 500);
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

function misplacedpin_current_client_id(): int
{
    $user = current_user();
    if (!is_array($user)) {
        return 0;
    }

    if (strtoupper((string) ($user['role'] ?? '')) !== 'CLIENT') {
        misplacedpin_json(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    return (int) ($user['user_id'] ?? 0);
}

// ─── Actions ──────────────────────────────────────────────────────────────────

function misplacedpin_save_result(int $clientId): void
{
    $payload = misplacedpin_payload();
    $level = max(0, (int) ($payload['level'] ?? 0));
    $points = max(0, (int) ($payload['points'] ?? 0));
    $expectedPoints = $level * 10;

    if ($points !== $expectedPoints || $level > 6553) {
        misplacedpin_json(['success' => false, 'message' => 'Invalid result data.'], 422);
    }

    $result = game_result_save_to_pending_or_insert($clientId, 'Misplacedpin', [
        'is_completed' => $level >= 30 ? 1 : 0,
        'time_seconds' => null,
        'points' => $points,
        'moves' => null,
        'difficulty' => null,
        'level' => $level,
        'time_per_level' => null,
        'levels_passed' => null,
    ]);

    misplacedpin_json([
        'success' => true,
        'result_id' => $result['id'],
    ]);
}

function misplacedpin_history(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT id, played_at, is_completed, points, level
         FROM game_results
         WHERE client_id = :client_id AND game_name = :game_name
         ORDER BY played_at DESC, id DESC
         LIMIT 50'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Misplacedpin',
    ]);

    misplacedpin_json([
        'success' => true,
        'sessions' => $stmt->fetchAll(),
    ]);
}

function misplacedpin_count(int $clientId): void
{
    $stmt = db()->prepare(
        'SELECT COUNT(*)
         FROM game_results
         WHERE client_id = :client_id AND game_name = :game_name'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => 'Misplacedpin',
    ]);

    misplacedpin_json([
        'success' => true,
        'count' => (int) $stmt->fetchColumn(),
    ]);
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * @return array<string,mixed>
 */
function misplacedpin_payload(): array
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
function misplacedpin_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
