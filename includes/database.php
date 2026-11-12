<?php
declare(strict_types=1);

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $path = __DIR__ . '/../database/barcelona.sqlite';

    if (!file_exists($path)) {
        throw new RuntimeException(
            'Database missing. Run php scripts/setup.php first.'
        );
    }

    $connection = new PDO('sqlite:' . $path);

    $connection->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $connection->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $connection->exec('PRAGMA foreign_keys = ON');
    $connection->exec('PRAGMA busy_timeout = 5000');

    return $connection;
}
