<?php
/* WF01 Home: welcome banner and the next upcoming matches (UC 06 preview). */
$pageTitle = "Home";
require_once __DIR__ . "/includes/header.php";
$role = $_SESSION["role"] ?? "guest";
$upcoming = array_slice($matches, 0, 2, true);
?>
<?php require __DIR__ . "/includes/messages.php"; ?>

<section class="fcb-hero text-center p-5 mb-4">
    <h1 class="fw-bold">FC Barcelona Ticketing System</h1>
    <p class="lead mb-4">View upcoming matches and find available tickets</p>
    <a class="btn btn-light btn-lg px-4" href="matches.php">View Matches</a>
</section>

<section class="fcb-panel p-4 mb-4" aria-labelledby="upcomingTitle">
    <h2 class="h5 fw-bold mb-3" id="upcomingTitle">Upcoming Matches</h2>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col">Opponent</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                    <th scope="col">Match Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming as $match): ?>
                    <tr>
                        <td><?= e($match["opponent"]) ?></td>
                        <td><?= e(format_date($match["date"])) ?></td>
                        <td><?= e(format_time($match["time"])) ?></td>
                        <td><span class="badge badge-<?= strtolower(e($match["type"])) ?>"><?= e($match["type"]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<p class="text-center">
    <?php if ($role === "user"): ?>
        <a href="my-account.php">My Account</a>
    <?php elseif ($role === "admin"): ?>
        <a href="manage-matches.php">Manage Matches</a>
    <?php else: ?>
        <a href="login.php">Login/Register</a>
    <?php endif; ?>
</p>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
