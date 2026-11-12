
<?php
/*
 * WF06 Create Match (UC 05), administrator only.
 *
 * Midterm demonstration: Matches are stored in PHP sessions.
 * Final project: Replace session storage with database operations.
 */

require_once __DIR__ . "/includes/init.php";

$error = "";

$match = [
    "opponent" => "",
    "date" => "",
    "time" => "",
    "type" => ""
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve submitted information.
    $match["opponent"] = trim($_POST["opponent"] ?? "");
    $match["date"] = trim($_POST["match_date"] ?? "");
    $match["time"] = trim($_POST["match_time"] ?? "");
    $match["type"] = trim($_POST["match_type"] ?? "");

    // Validate required fields.
    if (
        $match["opponent"] === "" ||
        $match["date"] === "" ||
        $match["time"] === "" ||
        $match["type"] === ""
    ) {
        $error = "Please complete all required fields.";
    }

    // Validate match date and time.
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
    if ($error === "" && !in_array($match["type"], $matchTypes, true)) {
        $error = "Match Type must be League or Cup.";
    }

    // Prevent two matches from sharing the same date and time.
    if ($error === "") {
        foreach ($_SESSION["demo_matches"] as $existingMatch) {
            if (
                $existingMatch["date"] === $match["date"] &&
                $existingMatch["time"] === $match["time"]
            ) {
                $error = "Another match is already scheduled for this date and time.";
                break;
            }
        }
    }

    // Save the match if validation succeeds.
    if ($error === "") {

        $newMatchId = $_SESSION["demo_next_match_id"]
            ?? (max(array_keys($_SESSION["demo_matches"]) ?: [0]) + 1);

        $_SESSION["demo_next_match_id"] = $newMatchId + 1;

        $_SESSION["demo_matches"][$newMatchId] = $match;

        redirect("manage-matches.php?msg=match_created");
    }
}

$pageTitle = "Create Match";
$pageRole = "admin";
$navActive = "manage-matches.php";
$submitLabel = "Create Match";

require_once __DIR__ . "/includes/header.php";
?>

<div class="fcb-panel fcb-form mx-auto p-4 p-md-5">

    <h1 class="h3 fw-bold text-center mb-4">
        Create Match
    </h1>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="create-match.php">
        <?php require __DIR__ . "/includes/match-form.php"; ?>
    </form>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
