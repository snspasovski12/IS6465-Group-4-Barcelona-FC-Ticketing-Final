<?php
declare(strict_types=1);

$databasePath = __DIR__ . '/../database/barcelona.sqlite';
$schemaPath = __DIR__ . '/../database/schema.sql';

if (file_exists($databasePath)) {
    echo "Database already exists. Setup stopped to protect data.\n";
    exit(0);
}

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$schema = file_get_contents($schemaPath);

if ($schema === false) {
    throw new RuntimeException('Could not read schema.sql');
}

$pdo->exec($schema);

echo "Database created successfully.\n";
echo "Location: database/barcelona.sqlite\n";
