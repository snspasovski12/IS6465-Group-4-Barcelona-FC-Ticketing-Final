<?php
/*
 * nav.php: the navigation bar. The links change with the role
 * ("Navigation changes based on logged-in user's role", WF05).
 *   guest: Home, Matches, Login                      (WF01, WF02, WF03)
 *   user:  Home, Matches, My Account, Logout         (WF04, WF07)
 *   admin: Home, Matches, Manage Users, Manage Matches, Logout (WF04A, WF06, WF07A, WF08)
 * A page can force a role with $pageRole; otherwise the session decides.
 */
$navRole = $pageRole ?? ($_SESSION["role"] ?? "guest");

$navLinks = ["index.php" => "Home", "matches.php" => "Matches"];
if ($navRole === "admin") {
    $navLinks += ["manage-users.php" => "Manage Users", "manage-matches.php" => "Manage Matches", "logout.php" => "Logout"];
} elseif ($navRole === "user") {
    $navLinks += ["my-account.php" => "My Account", "logout.php" => "Logout"];
} else {
    $navLinks += ["login.php" => "Login"];
}

$currentPage = basename($_SERVER["SCRIPT_NAME"]);
$navActive = $navActive ?? $currentPage;
?>
<header>
    <nav class="navbar navbar-expand-md navbar-dark fcb-navbar">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="fcb-crest" aria-hidden="true"></span>FC Barcelona
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <?php foreach ($navLinks as $href => $label): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $href === $navActive ? " active" : "" ?>" href="<?= e($href) ?>"
                                <?= $href === $navActive ? 'aria-current="page"' : "" ?>><?= e($label) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
