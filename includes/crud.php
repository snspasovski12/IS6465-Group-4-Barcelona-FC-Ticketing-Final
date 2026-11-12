<?php
require_once __DIR__ . '/permissions.php';
function fail_if(bool $condition, string $message): void { if ($condition) throw new RuntimeException($message); }
function input(string $name): string { return trim((string)($_POST[$name] ?? '')); }
function check_unique_user(string $username, string $email, int $except = 0): void {
    fail_if(db_one('SELECT UserID FROM users WHERE (Username = ? OR Email = ?) AND UserID != ?', [$username,$email,$except]) !== null, 'Username or email is already in use.');
}
function save_user(?int $id, bool $admin): int {
    $first=input('first'); $last=input('last'); $username=input('username'); $email=input('email'); $contact=input('contact');
    fail_if($first==='' || $last==='' || $username==='' || !valid_email($email), 'Enter a first name, last name, username, and valid email.');
    fail_if(strlen($username)>50 || strlen($first)>80 || strlen($last)>80 || strlen($email)>254, 'One or more fields are too long.');
    check_unique_user($username,$email,$id??0);
    if ($id === null) {
        $password=(string)($_POST['password']??'');
        fail_if(strlen($password)<10, 'Password must be at least 10 characters.');
        $role=$admin && input('role')==='Admin'?'Admin':'Fan';
        db_execute('INSERT INTO users (Username,Email,PasswordHash,FirstName,LastName,ContactInformation,Role,AccountStatus) VALUES (?,?,?,?,?,?,?,?)', [$username,$email,password_hash($password,PASSWORD_DEFAULT),$first,$last,$contact,$role,'Active']);
        return (int)db()->lastInsertId();
    }
    $old=db_one('SELECT * FROM users WHERE UserID=?',[$id]); fail_if(!$old,'User not found.');
    $role=$admin && in_array(input('role'),['Fan','Admin'],true)?input('role'):$old['Role'];
    $status=$admin && in_array(input('status'),['Active','Inactive'],true)?input('status'):$old['AccountStatus'];
    db_execute('UPDATE users SET Username=?,Email=?,FirstName=?,LastName=?,ContactInformation=?,Role=?,AccountStatus=? WHERE UserID=?',[$username,$email,$first,$last,$contact,$role,$status,$id]);
    return $id;
}
function save_match(?int $id): int {
    $opponent=input('opponent'); $date=input('date'); $time=input('time'); $type=input('type'); $status=input('status');
    fail_if($opponent==='' || strlen($opponent)>100 || !validate_match_date($date,$time), 'Enter a valid opponent, date, and time.');
    fail_if(!in_array($type,['League','Cup'],true) || !in_array($status,['Scheduled','Completed','Cancelled'],true), 'Invalid match type or status.');
    if ($id===null) {
        db_execute('INSERT INTO matches (Opponent,Date,Time,MatchType,MatchStatus) VALUES (?,?,?,?,?)',[$opponent,$date,$time,$type,$status]);
        return (int)db()->lastInsertId();
    }
    fail_if(!db_one('SELECT MatchID FROM matches WHERE MatchID=?',[$id]), 'Match not found.');
    db_execute('UPDATE matches SET Opponent=?,Date=?,Time=?,MatchType=?,MatchStatus=? WHERE MatchID=?',[$opponent,$date,$time,$type,$status,$id]);
    return $id;
}
function save_seat(?int $id): int {
    $section=input('section'); $row=filter_var(input('row'),FILTER_VALIDATE_INT); $number=filter_var(input('number'),FILTER_VALIDATE_INT);
    $price=filter_var(input('price'),FILTER_VALIDATE_FLOAT); $status=input('status');
    fail_if($section==='' || strlen($section)>30 || $row===false || $row<1 || $number===false || $number<1 || $price===false || $price<0 || !is_finite((float)$price), 'Enter a valid section, positive row/number, and nonnegative price.');
    fail_if(!in_array($status,['Active','Inactive'],true), 'Invalid seat status.');
    fail_if(db_one('SELECT SeatID FROM seats WHERE Section=? AND Row=? AND Number=? AND SeatID != ?',[$section,$row,$number,$id??0])!==null, 'That seat already exists.');
    fail_if(db_one('SELECT SeatID FROM seats WHERE Section=? AND ABS(Price-?)>0.001 AND SeatID != ? LIMIT 1',[$section,$price,$id??0])!==null, 'Seats in the same section must have the same price.');
    if ($id===null) {
        db_execute('INSERT INTO seats (Section,Row,Number,Price,SeatStatus) VALUES (?,?,?,?,?)',[$section,$row,$number,$price,$status]);
        return (int)db()->lastInsertId();
    }
    fail_if(!db_one('SELECT SeatID FROM seats WHERE SeatID=?',[$id]), 'Seat not found.');
    if (seat_has_history($id)) {
        $old=db_one('SELECT * FROM seats WHERE SeatID=?',[$id]);
        fail_if($old['Section']!==$section || (int)$old['Row']!==$row || (int)$old['Number']!==$number || abs((float)$old['Price']-(float)$price)>0.001, 'Historical seat location and price cannot be changed.');
    }
    db_execute('UPDATE seats SET Section=?,Row=?,Number=?,Price=?,SeatStatus=? WHERE SeatID=?',[$section,$row,$number,$price,$status,$id]);
    return $id;
}
function show_error(?string $message): void { if ($message) echo '<div class="alert alert-danger" role="alert">'.e($message).'</div>'; }
function csrf_field(): void { echo '<input type="hidden" name="csrf_token" value="'.e(csrf_token()).'">'; }
