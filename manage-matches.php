
<?php
/*
 * WF07A Manage Matches (UC 06 for an administrator).
 * Starts UC 05 (Create Match), UC 07 (Edit), and UC 08 (Delete).
 *
 * Midterm demonstration: Matches are stored in PHP sessions.
 * Final project: Replace session storage with database operations.
 */

require_once __DIR__ . "/includes/init.php";

// Process a confirmed Delete Match request.
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete"
) {

    // Retrieve and validate the selected Match ID.
    $deleteMatchId = filter_input(
        INPUT_POST,
        "match_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    // Check that the selected match exists.
    if (
        $deleteMatchId === false ||
        $deleteMatchId === null ||
        !isset($_SESSION["demo_matches"][$deleteMatchId])
    ) {
        redirect("manage-matches.php");
    }

    // Remove the match from session-backed demonstration data.
    unset($_SESSION["demo_matches"][$deleteMatchId]);

    // Return to Manage Matches with a success message.
    redirect("manage-matches.php?msg=match_deleted");
}

$pageTitle = "Manage Matches";
$pageRole = "admin";

require_once __DIR__ . "/includes/header.php";
?>

<div class="fcb-panel p-4 p-md-5">

    <h1 class="h3 fw-bold text-center mb-4">
        Manage Matches
    </h1>

    <?php require __DIR__ . "/includes/messages.php"; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2 class="h5 fw-bold mb-0">
            Upcoming FC Barcelona Matches
        </h2>

        <a class="btn btn-primary" href="create-match.php">
            Create Match
        </a>

    </div>

    <?php
    // Display the match list with Edit and Delete actions.
    $showActions = true;
    require __DIR__ . "/includes/match-table.php";
    ?>

    <p class="small fst-italic text-body-secondary mb-0">
        Delete requires confirmation before removing a match.
    </p>

</div>

<?php
// Display the existing Delete confirmation modal.
$modalTitle = "Delete match?";
$modalBody = "Delete the match";
$modalField = "match_id";
$modalAction = "manage-matches.php";

require __DIR__ . "/includes/delete-modal.php";
require_once __DIR__ . "/includes/footer.php";
?>
