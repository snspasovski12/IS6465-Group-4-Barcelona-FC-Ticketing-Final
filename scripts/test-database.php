<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/database.php';

$pdo = db();

$tables = ['users', 'matches', 'seats', 'tickets', 'transactions'];

foreach ($tables as $table) {
    $count = (int)$pdo->query(
        "SELECT COUNT(*) FROM $table"
    )->fetchColumn();

    echo "$table: $count records\n";
}

$foreignKeys = $pdo->query(
    'PRAGMA foreign_keys'
)->fetchColumn();

echo "Foreign keys enabled: " . ($foreignKeys ? 'YES' : 'NO') . "\n";

$user = $pdo->query(
    "SELECT Username, PasswordHash FROM users WHERE Username = 'admin'"
)->fetch();

echo "Admin password verification: " .
    ($user && password_verify('AdminDemo2026!', $user['PasswordHash'])
        ? 'PASS' : 'FAIL') . "\n";
