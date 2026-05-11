<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

$host = $config['host'];
$db = $config['db'];
$user = $config['user'];
$pass = $config['pass'];
$charset = $config['charset'];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

function db(): PDO
{
    /** @var PDO $pdo */
    global $pdo;
    return $pdo;
}

