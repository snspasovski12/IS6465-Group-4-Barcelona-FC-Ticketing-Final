<?php
require_once __DIR__.'/crud.php';
$kind=$kind??'match'; $admin=is_admin();
if ($kind==='user' || $kind==='seat' || $kind==='adminmatch') require_admin();
$table=match($kind){'user'=>'users','seat'=>'seats',default=>'matches'};
$key=match($kind){'user'=>'UserID','seat'=>'SeatID',default=>'MatchID'};
$title=match($kind){'user'=>'Manage User Accounts','seat'=>'Manage Seats','adminmatch'=>'Manage Matches',default=>'Available Matches'};
$back=match($kind){'user'=>'manage-users.php','seat'=>'manage-seats.php','adminmatch'=>'manage-matches.php',default=>'matches.php'};
$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_admin(); verify_csrf();
    try {
        $id=filter_var($_POST['record_id']??null,FILTER_VALIDATE_INT);
        fail_if(!$id || $id<1,'Invalid record ID.');
        $record=db_one("SELECT * FROM $table WHERE $key=?",[$id]); fail_if(!$record,'Record not found.');
        if ($kind==='user') {
            if (user_has_history($id)) db_execute("UPDATE users SET AccountStatus='Inactive' WHERE UserID=?",[$id]);
            else db_execute('DELETE FROM users WHERE UserID=?',[$id]);
        } elseif ($kind==='seat') {
            if (seat_has_history($id)) db_execute("UPDATE seats SET SeatStatus='Inactive' WHERE SeatID=?",[$id]);
            else db_execute('DELETE FROM seats WHERE SeatID=?',[$id]);
        } elseif ($kind==='adminmatch') {
            fail_if(match_has_history($id),'This match has ticket history and cannot be deleted. Cancel its status instead.');
            db_execute('DELETE FROM matches WHERE MatchID=?',[$id]);
        }
        flash('Record updated successfully.'); redirect($back);
    } catch (Throwable $ex) { $error=$ex->getMessage(); }
}
if ($kind==='user') $rows=db_all('SELECT * FROM users ORDER BY UserID');
elseif ($kind==='seat') $rows=db_all('SELECT * FROM seats ORDER BY Section,Row,Number');
else $rows=db_all('SELECT * FROM matches ORDER BY Date,Time');
$pageTitle=$title; require __DIR__.'/header.php';
$notice=take_flash();
?>

<?php
if ($kind === 'seat') {
    $selectedSection = trim((string)($_GET['section_filter'] ?? ''));
    $selectedStatus = trim((string)($_GET['status_filter'] ?? ''));

    if (!in_array($selectedStatus, ['', 'Active', 'Inactive'], true)) {
        $selectedStatus = '';
    }

    $seatSections = array_column(
        db_all('SELECT DISTINCT Section FROM seats ORDER BY Section'),
        'Section'
    );

    $rows = array_values(array_filter(
        $rows,
        static function (array $seat) use ($selectedSection, $selectedStatus): bool {
            $sectionMatches = $selectedSection === ''
                || (string)$seat['Section'] === $selectedSection;

            $statusMatches = $selectedStatus === ''
                || $seat['SeatStatus'] === $selectedStatus;

            return $sectionMatches && $statusMatches;
        }
    ));
}
?>
<h1 class="h3 mb-3"><?=e($title)?></h1>
<?php if($notice): ?><div class="alert alert-success"><?=e($notice)?></div><?php endif; ?>
<?php show_error($error); ?>
<?php if($kind==='adminmatch'): ?><p><a class="btn btn-primary" href="create-match.php">Create Match</a> <a class="btn btn-outline-primary" href="manage-tickets.php">Manage Tickets</a> <a class="btn btn-outline-primary" href="manage-transactions.php">Manage Transactions</a></p><?php endif; ?>
<?php if($kind==='user'): ?><p><a class="btn btn-primary" href="create-user.php">Create User</a></p><?php endif; ?>
<?php if($kind==='seat'): ?><p><a class="btn btn-primary" href="create-seat.php">Add Seat</a></p><?php endif; ?>

<?php if ($kind === 'seat'): ?>
<form method="get" action="manage-seats.php"
      class="row g-3 align-items-end mb-3">

    <div class="col-md-4">
        <label for="section_filter" class="form-label">Section</label>
        <select id="section_filter" name="section_filter" class="form-select">
            <option value="">All</option>
            <?php foreach ($seatSections as $section): ?>
                <option value="<?= e($section) ?>"
                    <?= $selectedSection === (string)$section ? 'selected' : '' ?>>
                    <?= e($section) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label for="status_filter" class="form-label">Status</label>
        <select id="status_filter" name="status_filter" class="form-select">
            <option value="">All</option>
            <option value="Active"
                <?= $selectedStatus === 'Active' ? 'selected' : '' ?>>
                Active
            </option>
            <option value="Inactive"
                <?= $selectedStatus === 'Inactive' ? 'selected' : '' ?>>
                Inactive
            </option>
        </select>
    </div>

    <div class="col-md-4">
        <button type="submit" class="btn btn-outline-primary">
            Search
        </button>
        <a href="manage-seats.php" class="btn btn-outline-secondary">
            Clear
        </a>
    </div>
</form>
<?php endif; ?>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr>
<?php if($kind==='user'): ?><th>Username</th><th>Full Name</th><th>Email</th><th>Contact</th><th>Status</th><th>Role</th>
<?php elseif($kind==='seat'): ?><th>Section</th><th>Row</th><th>Number</th><th>Price</th><th>Status</th>
<?php else: ?><th>Opponent</th><th>Date</th><th>Time</th><th>Match Type</th><th>Match Status</th><?php endif; ?>
<?php if($kind!=='match'): ?><th>Actions</th><?php endif; ?>
</tr></thead><tbody>
<?php foreach($rows as $r): ?><tr>
<?php if($kind==='user'): ?>
<td><?=e($r['Username'])?></td><td><?=e($r['FirstName'].' '.$r['LastName'])?></td><td><?=e($r['Email'])?></td><td><?=e($r['ContactInformation'])?></td><td><?=e($r['AccountStatus'])?></td><td><?=e($r['Role'])?></td>
<?php elseif($kind==='seat'): ?>
<td><?=e($r['Section'])?></td><td><?=e($r['Row'])?></td><td><?=e($r['Number'])?></td><td>$<?=number_format((float)$r['Price'],2)?></td><td><?=e($r['SeatStatus'])?></td>
<?php else: ?>
<td><?php if($kind==='match' && $r['MatchStatus']==='Scheduled' && $r['Date'].' '.$r['Time'] > date('Y-m-d H:i')): ?><a href="reserve-seat.php?match_id=<?=(int)$r['MatchID']?>"><?=e($r['Opponent'])?></a><?php else: ?><?=e($r['Opponent'])?><?php endif; ?></td>
<td><?=e(format_date($r['Date']))?></td><td><?=e(format_time($r['Time']))?></td><td><?=e($r['MatchType'])?></td><td><?=e($r['MatchStatus'])?></td>
<?php endif; ?>
<?php if($kind!=='match'): ?>
<td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="<?=e(match($kind){'user'=>'update-user.php?user_id=','seat'=>'update-seat.php?seat_id=',default=>'update-match.php?match_id='})?><?=(int)$r[$key]?>">Edit</a>
<form class="d-inline" method="post" onsubmit="return confirm('Confirm delete/deactivation?');"><?php csrf_field(); ?><input type="hidden" name="record_id" value="<?=(int)$r[$key]?>"><button class="btn btn-sm btn-outline-danger" type="submit">Delete</button></form></td>
<?php endif; ?>
</tr><?php endforeach; ?></tbody></table></div>
<?php if(!$rows): ?><p class="alert alert-info">No records found.</p><?php endif; ?>
<?php require __DIR__.'/footer.php'; ?>
