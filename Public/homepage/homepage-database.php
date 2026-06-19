<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';

// ─── Stats ────────────────────────────────────────────────────────────────────

function getClientCount(): array
{
    $total = (int) db()->query("
        SELECT COUNT(*) 
        FROM users
        WHERE role = 'CLIENT'
    ")->fetchColumn();

    return [
        'total' => $total,
    ];
}

function getTherapistCount(): array
{
    $total = (int) db()->query("
        SELECT COUNT(*) 
        FROM users
        WHERE role = 'THERAPIST'
    ")->fetchColumn();

    return [
        'total' => $total,
    ];
}

function getRate(): array
{
    $avg = (float) db()->query("
        SELECT AVG(rating) 
        FROM therapists
    ")->fetchColumn();

    $percentage = ($avg / 5) * 100;

    return [
        'total' => round($percentage),
    ];
}

function getAverageRating(): array
{
    $avg = (float) db()->query("
        SELECT AVG(rating) 
        FROM therapists
    ")->fetchColumn();

    return [
        'total' => round($avg, 1),
    ];
}

function getSessionCount(): array
{
    $total = (int) db()->query("
        SELECT COUNT(*) 
        FROM appointments
        WHERE status = 'COMPLETED'
    ")->fetchColumn();

    return [
        'total' => $total,
    ];
}