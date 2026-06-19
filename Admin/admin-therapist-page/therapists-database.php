<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';

// ─── Stats ────────────────────────────────────────────────────────────────────

function getTherapistsStats(): array
{
    $row = db()->query("
        SELECT
            COUNT(*) AS total,
            SUM(status = 'AVAILABLE') AS available,
            ROUND(AVG(rating), 1) AS avg_rating
        FROM therapists
    ")->fetch();

    $casesRow = db()->query("
        SELECT COUNT(*) AS total_cases FROM cases
    ")->fetch();

    return [
        'total' => (int)   ($row['total'] ?? 0),
        'available' => (int)   ($row['available'] ?? 0),
        'avg_rating' => (float) ($row['avg_rating'] ?? 0.0),
        'total_cases' => (int)   ($casesRow['total_cases'] ?? 0),
    ];
}

// ─── Fetch All ────────────────────────────────────────────────────────────────

function getAllTherapists(): array
{
    $stmt = db()->query("
        SELECT
            t.therapist_id,
            u.name,
            u.email,
            t.specialization,
            t.experience_years,
            t.rating,
            t.status,
            COUNT(c.case_id) AS total_cases
        FROM therapists t
        INNER JOIN users u ON u.user_id = t.therapist_id
        LEFT  JOIN cases c ON c.therapist_id = t.therapist_id
        GROUP BY
            t.therapist_id, u.name, u.email,
            t.specialization, t.experience_years, t.rating, t.status
        ORDER BY u.created_at DESC
    ");
    return $stmt->fetchAll();
}

// ─── Create ───────────────────────────────────────────────────────────────────

function addTherapist(array $data): bool
{
    $pdo = db();
    $username = strtolower(explode('@', $data['email'])[0]) . '_' . time();
    $password = password_hash('123456789', PASSWORD_DEFAULT);

    $pdo->beginTransaction();
    try {
        $pdo->prepare("
            INSERT INTO users (name, username, email, password, role)
            VALUES (:name, :username, :email, :password, 'THERAPIST')
        ")->execute([
            ':name'     => $data['name'],
            ':username' => $username,
            ':email'    => $data['email'],
            ':password' => $password,
        ]);

        $newId = (int) $pdo->lastInsertId();

        $pdo->prepare("
            INSERT INTO therapists
                (therapist_id, specialization, experience_years, rating, status, bio, certification)
            VALUES
                (:id, :spec, :exp, :rating, :status, '', 'N/A')
        ")->execute([
            ':id'     => $newId,
            ':spec'   => $data['specialization'],
            ':exp'    => (int)   $data['experience_years'],
            ':rating' => (float) $data['rating'],
            ':status' => $data['status'],
        ]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// ─── Update ───────────────────────────────────────────────────────────────────

function updateTherapist(int $id, array $data): bool
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare("
            UPDATE users
            SET name = :name, email = :email
            WHERE user_id = :id
        ")->execute([
            ':name'  => $data['name'],
            ':email' => $data['email'],
            ':id'    => $id,
        ]);

        $pdo->prepare("
            UPDATE therapists
            SET specialization   = :spec,
                experience_years = :exp,
                rating           = :rating,
                status           = :status
            WHERE therapist_id = :id
        ")->execute([
            ':spec'   => $data['specialization'],
            ':exp'    => (int)   $data['experience_years'],
            ':rating' => (float) $data['rating'],
            ':status' => $data['status'],
            ':id'     => $id,
        ]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// ─── Delete ───────────────────────────────────────────────────────────────────

function deleteTherapist(int $id): bool
{
    $stmt = db()->prepare("
        DELETE FROM users WHERE user_id = :id AND role = 'THERAPIST'
    ");
    return $stmt->execute([':id' => $id]);
}
