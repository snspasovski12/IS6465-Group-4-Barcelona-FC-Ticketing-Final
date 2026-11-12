<?php
$user = current_user();
$role = $user['Role'] ?? 'Guest';

$navLinks = [
    'index.php' => 'Home',
    ($role === 'Admin' ? 'manage-matches.php' : 'matches.php') => 'Matches'
];

if ($role === 'Admin') {
    $navLinks['manage-seats.php'] = 'Seats';
    $navLinks['manage-users.php'] = 'Users';
    $navLinks['logout.php'] = 'Logout';
} elseif ($role === 'Fan') {
    $navLinks['my-account.php'] = 'My Account';
    $navLinks['logout.php'] = 'Logout';
} else {
    $navLinks['login.php'] = 'Login';
}

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<header>
<nav class="navbar navbar-expand-md navbar-dark fcb-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <span class="fcb-crest" aria-hidden="true"></span>
            FC Barcelona
        </a>

        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
            <?php foreach ($navLinks as $href => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $href === $currentPage ? 'active' : '' ?>"
                       href="<?= e($href) ?>">
                        <?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
</header>
