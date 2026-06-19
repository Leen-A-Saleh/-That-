<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function game_result_pending_id(int $clientId, string $gameName): int
{
    $stmt = db()->prepare(
        'SELECT id
         FROM game_results
         WHERE client_id = :client_id
           AND game_name = :game_name
           AND is_completed = 0
         ORDER BY id DESC
         LIMIT 1'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => $gameName,
    ]);

    return (int) ($stmt->fetchColumn() ?: 0);
}

/**
 * @return array{id:int,created:bool}
 */
function game_result_start_or_touch_pending(int $clientId, string $gameName): array
{
    $resultId = game_result_pending_id($clientId, $gameName);
    if ($resultId > 0) {
        $stmt = db()->prepare(
            'UPDATE game_results
             SET played_at = NOW()
             WHERE id = :id
               AND client_id = :client_id
               AND game_name = :game_name
               AND is_completed = 0'
        );
        $stmt->execute([
            'id' => $resultId,
            'client_id' => $clientId,
            'game_name' => $gameName,
        ]);

        return ['id' => $resultId, 'created' => false];
    }

    $stmt = db()->prepare(
        'INSERT INTO game_results
            (client_id, game_name, played_at, is_completed, time_seconds, points, moves, difficulty, level, time_per_level, levels_passed)
         VALUES
            (:client_id, :game_name, NOW(), 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL)'
    );
    $stmt->execute([
        'client_id' => $clientId,
        'game_name' => $gameName,
    ]);

    return ['id' => (int) db()->lastInsertId(), 'created' => true];
}

/**
 * @param array{
 *   is_completed:int,
 *   time_seconds?:int|null,
 *   points?:int|null,
 *   moves?:int|null,
 *   difficulty?:string|null,
 *   level?:int|null,
 *   time_per_level?:string|null,
 *   levels_passed?:string|null
 * } $values
 * @return array{id:int,created:bool}
 */
function game_result_save_to_pending_or_insert(int $clientId, string $gameName, array $values): array
{
    $params = [
        'client_id' => $clientId,
        'game_name' => $gameName,
        'is_completed' => (int) ($values['is_completed'] ?? 0),
        'time_seconds' => $values['time_seconds'] ?? null,
        'points' => $values['points'] ?? null,
        'moves' => $values['moves'] ?? null,
        'difficulty' => $values['difficulty'] ?? null,
        'level' => $values['level'] ?? null,
        'time_per_level' => $values['time_per_level'] ?? null,
        'levels_passed' => $values['levels_passed'] ?? null,
    ];

    $resultId = game_result_pending_id($clientId, $gameName);
    if ($resultId > 0) {
        $stmt = db()->prepare(
            'UPDATE game_results
             SET played_at = NOW(),
                 is_completed = :is_completed,
                 time_seconds = :time_seconds,
                 points = :points,
                 moves = :moves,
                 difficulty = :difficulty,
                 level = :level,
                 time_per_level = :time_per_level,
                 levels_passed = :levels_passed
             WHERE id = :id
               AND client_id = :client_id
               AND game_name = :game_name'
        );
        $stmt->execute($params + ['id' => $resultId]);

        return ['id' => $resultId, 'created' => false];
    }

    $stmt = db()->prepare(
        'INSERT INTO game_results
            (client_id, game_name, played_at, is_completed, time_seconds, points, moves, difficulty, level, time_per_level, levels_passed)
         VALUES
            (:client_id, :game_name, NOW(), :is_completed, :time_seconds, :points, :moves, :difficulty, :level, :time_per_level, :levels_passed)'
    );
    $stmt->execute($params);

    return ['id' => (int) db()->lastInsertId(), 'created' => true];
}
