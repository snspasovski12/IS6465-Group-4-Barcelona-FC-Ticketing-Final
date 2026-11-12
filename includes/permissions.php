<?php
require_once __DIR__ . '/init.php';

function require_fan_or_admin(): array
{
    return require_login();
}

function require_owner_or_admin(int $userId): array
{
    $user = require_login();

    if ($user['Role'] !== 'Admin'
        && (int)$user['UserID'] !== $userId) {
        http_response_code(403);
        exit('You cannot access this account.');
    }

    return $user;
}

function require_active_match(int $matchId): array
{
    $match = db_one(
        'SELECT * FROM matches WHERE MatchID = ?',
        [$matchId]
    );

    if (!$match) {
        http_response_code(404);
        exit('Match not found.');
    }

    return $match;
}

function require_existing_seat(int $seatId): array
{
    $seat = db_one(
        'SELECT * FROM seats WHERE SeatID = ?',
        [$seatId]
    );

    if (!$seat) {
        http_response_code(404);
        exit('Seat not found.');
    }

    return $seat;
}
