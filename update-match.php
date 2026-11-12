
<?php
/*
 * WF08 Update Match (UC 07), administrator only.
 *
 * Midterm demonstration: Match updates use PHP session storage.
 * Final project: Replace session storage with database operations.
 */

require_once __DIR__ . "/includes/init.php";

$matchId = id_from_url("match_id");
$match = $matchId !== null ? ($matches[$matchId] ?? null) : null;

$error = "";

// Process the update only if the match exists.
if ($_SERVER["REQUEST_METHOD"] === "POST" && $match !== null) {

    // Retrieve submitted information.
    $updatedMatch = [
        "opponent" => trim($_POST["opponent"] ?? ""),
        "date" => trim($_POST["match_date"] ?? ""),
        "time" => trim($_POST["match_time"] ?? ""),
        "type" => trim($_POST["match_type"] ?? "")
    ];

    // Keep submitted values in the form if validation fails.
    $match = $updatedMatch;

    // Validate required fields.
    if (
        $match["opponent"] === "" ||
        $match["date"] === "" ||
        $match["time"] === "" ||
        $match["type"] === ""
    ) {
        $error = "Please complete all required fields.";
    }

    // Validate date and time formats.
    if ($error === "") {

        $date = DateTimeImmutable::createFromFormat(
            "!Y-m-d",
            $match["date"]
        );

        $time = DateTimeImmutable::createFromFormat(
            "!H:i",
            $match["time"]
        );

        if (
            !$date ||
            $date->format("Y-m-d") !== $match["date"] ||
            !$time ||
            $time->format("H:i") !== $match["time"]
        ) {
            $error = "Please enter a valid match date and time.";
        } elseif ($match["date"] < date("Y-m-d")) {
            $error = "Match date cannot be in the past.";
        }
    }

    // Validate Match Type.
    if (
        $error === "" &&
        !in_array($match["type"], $matchTypes, true)
    ) {
        $error = "Match Type must be League or Cup.";
    }

    // Check for scheduling conflicts with other matches.
    if ($error === "") {

        foreach ($_SESSION["demo_matches"] as $existingId => $existingMatch) {

            // Exclude the match currently being updated.
            if ((int) $existingId === (int) $matchId) {
                continue;
            }

            if (
                $existingMatch["date"] === $match["date"] &&
                $existingMatch["time"] === $match["time"]
            ) {
                $error = "Another match is already scheduled for this date and time.";
                break;
            }
        }
    }

    // Save the updated match if all validation passes.
    if ($error === "") {

        $_SESSION["demo_matches"][$matchId] = $updatedMatch;

        redirect("manage-matches.php?msg=match_updated");
    }
}

$pageTitle = "Update Match";
$pageRole = "admin";
$navActive = "manage-matches.php";
$submitLabel = "Update Match";

require_once __DIR__ . "/includes/header.php";
?>

<div class="fcb-panel fcb-form mx-auto p-4 p-md-5">

    <h1 class="h3 fw-bold text-center mb-4">
        Update Match
    </h1>

    <?php if ($match === null): ?>

        <div class="alert alert-warning" role="alert">
            Match not found. Choose a match from the list.
        </div>

        <a class="btn btn-outline-secondary" href="manage-matches.php">
            Back to Manage Matches
        </a>

    <?php else: ?>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post"
              action="update-match.php?match_id=<?= (int) $matchId ?>">

            <?php require __DIR__ . "/includes/match-form.php"; ?>

        </form>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
