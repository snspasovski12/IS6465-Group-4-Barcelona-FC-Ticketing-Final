<?php
require_once __DIR__.'/includes/init.php';
$pageTitle='Home'; $matches=db_all("SELECT * FROM matches WHERE MatchStatus='Scheduled' AND (Date || ' ' || Time) > ? ORDER BY Date,Time LIMIT 10",[date('Y-m-d H:i')]);
require __DIR__.'/includes/header.php';
?>
<section class="text-center py-4"><h1>FC Barcelona Ticketing System</h1><p>View upcoming matches and find available tickets.</p><a class="btn btn-primary" href="matches.php">View Matches</a></section>
<h2 class="h4 mt-4">Upcoming Matches</h2>
<table class="table"><thead><tr><th>Opponent</th><th>Date</th><th>Time</th><th>Match Type</th></tr></thead><tbody>
<?php foreach($matches as $m): ?><tr><td><?=e($m['Opponent'])?></td><td><?=e(format_date($m['Date']))?></td><td><?=e(format_time($m['Time']))?></td><td><?=e($m['MatchType'])?></td></tr><?php endforeach; ?>
</tbody></table>
<?php if(!current_user()): ?><a href="login.php">Login/Register</a><?php endif; ?>
<?php require __DIR__.'/includes/footer.php'; ?>
