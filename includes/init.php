<?php
declare(strict_types=1);

date_default_timezone_set('America/Denver');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function format_date(string $date): string
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed ? $parsed->format('D, M j, Y') : $date;
}

function format_time(string $time): string
{
    $parsed = DateTimeImmutable::createFromFormat('!H:i', $time);
    return $parsed ? $parsed->format('g:i A') : $time;
}

function id_from_url(string $name): ?int
{
    $value = filter_input(
        INPUT_GET,
        $name,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    return $value === false || $value === null ? null : $value;
}

function flash(string $message): void
{
    $_SESSION['flash_message'] = $message;
}

function take_flash(): ?string
{
    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return $message;
}

function require_post_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }

    verify_csrf();
}

function db_one(string $sql, array $parameters = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($parameters);
    return $statement->fetch() ?: null;
}

function db_all(string $sql, array $parameters = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($parameters);
    return $statement->fetchAll();
}

function db_execute(string $sql, array $parameters = []): int
{
    $statement = db()->prepare($sql);
    $statement->execute($parameters);
    return $statement->rowCount();
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['Role'] === 'Admin';
}

function validate_match_date(string $date, string $time): bool
{
    $dateTime = DateTimeImmutable::createFromFormat(
        '!Y-m-d H:i',
        $date . ' ' . $time
    );

    return $dateTime !== false
        && $dateTime->format('Y-m-d H:i') === $date . ' ' . $time;
}

function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function user_has_history(int $userId): bool
{
    return db_one(
        'SELECT TicketID FROM tickets WHERE UserID = ? LIMIT 1',
        [$userId]
    ) !== null
    || db_one(
        'SELECT TransactionID FROM transactions WHERE UserID = ? LIMIT 1',
        [$userId]
    ) !== null;
}

function match_has_history(int $matchId): bool
{
    return db_one(
        'SELECT TicketID FROM tickets WHERE MatchID = ? LIMIT 1',
        [$matchId]
    ) !== null;
}

function seat_has_history(int $seatId): bool
{
    return db_one(
        'SELECT TicketID FROM tickets WHERE SeatID = ? LIMIT 1',
        [$seatId]
    ) !== null;
}
