<?php
/* WF07 View Matches (UC 06 for a fan): match information displayed for viewing only. */
$pageTitle = "Matches";
require_once __DIR__ . "/includes/header.php";
?>
<div class="fcb-panel p-4 p-md-5">
    <h1 class="h3 fw-bold text-center mb-1">Available Matches</h1>
    <p class="text-center text-body-secondary mb-4">Upcoming FC Barcelona Matches</p>
    <?php
    $showActions = false;
    require __DIR__ . "/includes/match-table.php";
    ?>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
