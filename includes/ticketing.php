<?php
declare(strict_types=1);
require_once __DIR__ . '/init.php';
function ticket_now(): string { return gmdate('Y-m-d H:i:s'); }
function ticket_expire(): void {
    db_execute("UPDATE tickets SET TicketStatus='Expired' WHERE TicketStatus='Pending' AND ExpiresAt <= ?", [ticket_now()]);
}
function ticket_available(int $matchId, int $seatId): bool {
    ticket_expire();
    $match = db_one('SELECT * FROM matches WHERE MatchID=?', [$matchId]);
    $seat = db_one('SELECT * FROM seats WHERE SeatID=?', [$seatId]);
    if (!$match || !$seat || $match['MatchStatus'] !== 'Scheduled' || $seat['SeatStatus'] !== 'Active') return false;
    $kickoff = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $match['Date'].' '.$match['Time'], new DateTimeZone('America/Denver'));
    if (!$kickoff || $kickoff->getTimestamp() <= time()) return false;
    return !db_one("SELECT TicketID FROM tickets WHERE MatchID=? AND SeatID=? AND TicketStatus IN ('Pending','Purchased')", [$matchId,$seatId]);
}
function ticket_reserve(int $userId, int $matchId, int $seatId): int {
    $pdo = db(); $pdo->exec('BEGIN IMMEDIATE');
    try {
        if (!ticket_available($matchId,$seatId)) throw new RuntimeException('Seat is not available for this match.');
        $seat = db_one('SELECT Price FROM seats WHERE SeatID=?',[$seatId]);
        $now = ticket_now(); $expires = gmdate('Y-m-d H:i:s', time()+900);
        db_execute("INSERT INTO tickets(UserID,MatchID,SeatID,TicketStatus,ReservedAt,ExpiresAt,TicketPrice) VALUES(?,?,?,'Pending',?,?,?)", [$userId,$matchId,$seatId,$now,$expires,$seat['Price']]);
        $id = (int)$pdo->lastInsertId(); $pdo->commit(); return $id;
    } catch(Throwable $e) { if($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
function ticket_record(int $id): ?array {
    ticket_expire();
    return db_one('SELECT t.*,m.Opponent,m.Date,m.Time,m.MatchStatus,s.Section,s.Row,s.Number FROM tickets t JOIN matches m ON m.MatchID=t.MatchID JOIN seats s ON s.SeatID=t.SeatID WHERE t.TicketID=?',[$id]);
}
function ticket_pay(int $ticketId,int $userId,bool $success): void {
    $pdo=db(); $pdo->exec('BEGIN IMMEDIATE');
    try {
        ticket_expire();
        $t=ticket_record($ticketId);
        if (!$t || (int)$t['UserID']!==$userId || $t['TicketStatus']!=='Pending' || $t['ExpiresAt']<=ticket_now()) throw new RuntimeException('Reservation is unavailable or expired.');
        $status=$success?'Completed':'Failed';
        db_execute('INSERT INTO transactions(UserID,TicketID,Amount,PaymentMethod,TransactionStatus) VALUES(?,?,?,\'Simulated Payment\',?)',[$userId,$ticketId,$t['TicketPrice'],$status]);
        if ($success) db_execute("UPDATE tickets SET TicketStatus='Purchased' WHERE TicketID=?",[$ticketId]);
        $pdo->commit();
    } catch(Throwable $e) { if($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
function ticket_void(int $transactionId): void {
    $pdo=db(); $pdo->exec('BEGIN IMMEDIATE');
    try {
        $tx=db_one('SELECT * FROM transactions WHERE TransactionID=? AND DeletedAt IS NULL',[$transactionId]);
        if(!$tx || $tx['TransactionStatus']!=='Completed') throw new RuntimeException('Only completed transactions can be voided.');
        db_execute("UPDATE transactions SET TransactionStatus='Voided' WHERE TransactionID=?",[$transactionId]);
        db_execute("UPDATE tickets SET TicketStatus='Cancelled' WHERE TicketID=? AND TicketStatus='Purchased'",[$tx['TicketID']]);
        $pdo->commit();
    } catch(Throwable $e) { if($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
function ticket_id_label(int $id): string { return 'T'.str_pad((string)$id,4,'0',STR_PAD_LEFT); }
function transaction_id_label(int $id): string { return 'TX'.str_pad((string)$id,4,'0',STR_PAD_LEFT); }
