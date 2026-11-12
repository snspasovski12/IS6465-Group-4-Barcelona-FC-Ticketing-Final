<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/database.php';

$pdo = db();

if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
    echo "Users already exist. Seed skipped to protect data.\n";
    exit(0);
}

$pdo->beginTransaction();

try {
    $insertUser = $pdo->prepare(
        'INSERT INTO users
        (Username, Email, PasswordHash, FirstName, LastName,
         ContactInformation, Role, AccountStatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $demoUsers = [
        ['admin', 'admin@example.test', 'Admin', 'User', '', 'Admin'],
        ['fan', 'fan@example.test', 'Demo', 'Fan', '', 'Fan'],
        ['sspasovski', 'stefan@example.test', 'Stefan', 'Spasovski', '', 'Fan'],
        ['cosmond', 'cody@example.test', 'Cody', 'Osmond', '', 'Fan'],
        ['mahmed', 'mandeqa@example.test', 'Mandeqa', 'Ahmed', '', 'Fan'],
    ];

    foreach ($demoUsers as $user) {
        $insertUser->execute([
            $user[0],
            $user[1],
            password_hash(
                $user[5] === 'Admin' ? 'AdminDemo2026!' : 'FanDemo2026!',
                PASSWORD_DEFAULT
            ),
            $user[2],
            $user[3],
            $user[4],
            $user[5],
            'Active'
        ]);
    }

    $insertMatch = $pdo->prepare(
        'INSERT INTO matches
        (Opponent, Date, Time, MatchType, MatchStatus)
        VALUES (?, ?, ?, ?, ?)'
    );

    $demoMatches = [
        ['Real Madrid', '2026-11-13', '19:00', 'League', 'Scheduled'],
        ['Sevilla', '2026-11-28', '21:00', 'Cup', 'Completed'],
        ['Atletico Madrid', '2026-12-13', '15:00', 'League', 'Scheduled']
    ];

    foreach ($demoMatches as $match) {
        $insertMatch->execute($match);
    }

    $insertSeat = $pdo->prepare(
        'INSERT INTO seats
        (Section, Row, Number, Price, SeatStatus)
        VALUES (?, ?, ?, ?, ?)'
    );

    foreach ([10, 11, 12] as $number) {
        $insertSeat->execute(['108', 6, $number, 120.00, 'Active']);
    }

    $pdo->commit();

    echo "Seed data created successfully.\n";
    echo "Demo admin: admin / AdminDemo2026!\n";
    echo "Demo fan: fan / FanDemo2026!\n";
    echo "Change demo credentials before any public deployment.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
