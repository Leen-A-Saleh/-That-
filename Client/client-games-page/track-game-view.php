<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/game-results.php';

start_secure_session();
require_auth();
require_role(['CLIENT']);

header('Content-Type: application/json; charset=UTF-8');

$input = json_decode(file_get_contents("php://input"), true);

$key = trim((string)($input['key'] ?? ''));
$gameNames = [
    'Breathing' => 'Breathing',
    'Difference' => 'Difference',
    'Misplacedpin' => 'Misplacedpin',
    'Cards' => 'Cards',
    'Crossword' => 'Crossword',
    'Questions' => 'Questions',
];

if ($key === '' || !isset($gameNames[$key])) {
    echo json_encode(['success' => false, 'message' => 'Invalid key']);
    exit;
}

$pendingResult = game_result_start_or_touch_pending(client_current_user_id(), $gameNames[$key]);

$updatedRows = 0;
try {
    $stmt = db()->prepare("
        UPDATE activities
        SET views = views + 1
        WHERE game_key = :key
        LIMIT 1
    ");

    $stmt->execute([
        'key' => $key
    ]);
    $updatedRows = $stmt->rowCount();
} catch (Throwable) {
    $updatedRows = 0;
}

echo json_encode([
    'success' => true,
    'key' => $key,
    'result_id' => $pendingResult['id'],
    'created' => $pendingResult['created'],
    'updated_rows' => $updatedRows
]);
