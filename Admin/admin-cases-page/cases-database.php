<?php

declare(strict_types=1);
require_once __DIR__ . '/../../Database/db.php';

// ─── Stats ────────────────────────────────────────────────────────────────────

function getCasesStats(): array
{
    $sql = "SELECT 
                COUNT(*) AS total,
                SUM(status = 'IN_PROGRESS') AS active,
                SUM(status = 'UNDER_REVIEW') AS review,
                SUM(status = 'CLOSED') AS closed
            FROM cases";
    $row = db()->query($sql)->fetch();

    return [
        'total'  => (int)($row['total'] ?? 0),
        'active' => (int)($row['active'] ?? 0),
        'review' => (int)($row['review'] ?? 0),
        'closed' => (int)($row['closed'] ?? 0),
    ];
}

// ─── Queries ──────────────────────────────────────────────────────────────────

function getCases(int $page = 1, int $limit = 8, string $search = '', string $status = ''): array
{
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "u_client.name LIKE :search";
        $params[':search'] = "%$search%";
    }

    if ($status !== '') {
        $where[] = "c.status = :status";
        $params[':status'] = $status;
    }

    $whereSql = '';
    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $sql = "
        SELECT 
            c.case_id,
            c.status,
            c.progress,
            u_client.name AS client_name,
            u_thera.name AS therapist_name,
            MAX(s.end_time) AS last_session,
            COUNT(s.session_id) AS sessions_count
        FROM cases c
        JOIN users u_client ON c.client_id = u_client.user_id
        JOIN users u_thera ON c.therapist_id = u_thera.user_id
        LEFT JOIN sessions s ON s.case_id = c.case_id
        $whereSql
        GROUP BY 
            c.case_id, c.status, c.progress, 
            u_client.name, u_thera.name
        ORDER BY c.case_id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = db()->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countSql = "
        SELECT COUNT(DISTINCT c.case_id)
        FROM cases c
        JOIN users u_client ON c.client_id = u_client.user_id
        JOIN users u_thera ON c.therapist_id = u_thera.user_id
        LEFT JOIN sessions s ON s.case_id = c.case_id
        $whereSql
    ";

    $countStmt = db()->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $totalFound = (int) $countStmt->fetchColumn();

    return [
        'cases' => $cases,
        'totalFound' => $totalFound
    ];
}
