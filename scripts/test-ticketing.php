<?php
require_once __DIR__.'/../includes/ticketing.php';
$pdo=db();$pdo->exec('SAVEPOINT batch3_test');
try {
 $match=db_one("SELECT MatchID FROM matches WHERE MatchStatus='Scheduled' AND Date >= ? ORDER BY Date DESC LIMIT 1",[date('Y-m-d')]);
 $seat=db_one("SELECT SeatID FROM seats WHERE SeatStatus='Active' LIMIT 1");
 $user=db_one("SELECT UserID FROM users WHERE Role='Fan' LIMIT 1");
 if(!$match||!$seat||!$user)throw new RuntimeException('Missing eligible seed records');
 // Check database constraints without writing permanent sample transactions.
 $fk=(int)$pdo->query('PRAGMA foreign_keys')->fetchColumn();
 if($fk!==1)throw new RuntimeException('Foreign keys disabled');
 echo "Ticketing prerequisites PASS; foreign keys enabled; test changes rolled back.\n";
} finally {$pdo->exec('ROLLBACK TO SAVEPOINT batch3_test');$pdo->exec('RELEASE SAVEPOINT batch3_test');}
