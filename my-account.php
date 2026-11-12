
<?php
/*
 * WF04 View User Account (UC 02 for a fan).
 * Starts UC 03 (Update Account) and UC 04 (Delete Account).
 *
 * Midterm demonstration: User records are stored in PHP sessions.
 * User ID 1 has simulated ticket/transaction history.
 * Final project: Replace demonstration rules with database checks.
 */

require_once __DIR__ . "/includes/init.php";

// Process a confirmed Delete Account request.
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete"
) {
    $deleteUserId = filter_input(
        INPUT_POST,
        "user_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    // A fan may only delete their own account.
    if (
        $deleteUserId === false ||
        $deleteUserId === null ||
        $deleteUserId !== $currentUserId ||
        !isset($_SESSION["demo_users"][$deleteUserId])
    ) {
        redirect("my-account.php");
    }

    // Midterm demonstration:
    // User ID 1 has associated tickets or transactions.
    if ($deleteUserId === 1) {
        redirect("my-account.php?msg=user_delete_blocked");
    }

    // Remove the account from session-backed demonstration data.
    unset($_SESSION["demo_users"][$deleteUserId]);

    // Sign out after successful account deletion.
    $_SESSION = [];
    session_destroy();

    redirect("login.php?msg=account_deleted");
}

$pageTitle = "My Account";
$pageRole = "user";

require_once __DIR__ . "/includes/header.php";

// Retrieve the signed-in demonstration user's information.
$user = $users[$currentUserId] ?? null;
?>

<div class="fcb-panel fcb-form mx-auto p-4 p-md-5">

    <h1 class="h3 fw-bold text-center mb-4">
        My Account
    </h1>

    <?php require __DIR__ . "/includes/messages.php"; ?>

    <?php if ($user === null): ?>

        <div class="alert alert-warning" role="alert">
            Account not found.
        </div>

        <a class="btn btn-outline-secondary" href="index.php">
            Return Home
        </a>

    <?php else: ?>

        <dl class="row mb-4">
            <dt class="col-sm-4">Full Name</dt>
            <dd class="col-sm-8"><?= e($user["full_name"]) ?></dd>

            <dt class="col-sm-4">Username</dt>
            <dd class="col-sm-8"><?= e($user["username"]) ?></dd>

            <dt class="col-sm-4">Email</dt>
            <dd class="col-sm-8"><?= e($user["email"]) ?></dd>

            <dt class="col-sm-4">Contact Information</dt>
            <dd class="col-sm-8"><?= e($user["contact"]) ?></dd>
        </dl>

        <div class="d-flex flex-wrap gap-2">

            <a class="btn btn-primary"
               href="update-user.php?user_id=<?= (int) $currentUserId ?>">
                Update Account
            </a>

            <button
                type="button"
                class="btn btn-outline-danger"
                data-bs-toggle="modal"
                data-bs-target="#deleteModal">
                Delete Account
            </button>

        </div>

        <p class="small fst-italic text-body-secondary mt-3 mb-0">
            Delete Account requires confirmation before deletion.
        </p>

    <?php endif; ?>

</div>

<?php
if ($user !== null) {
    $modalTitle = "Delete your account?";
    $modalBody = "Delete the account";
    $modalField = "user_id";
    $modalAction = "my-account.php";
    $modalDefaultId = $currentUserId;
    $modalDefaultName = $user["username"];

    require __DIR__ . "/includes/delete-modal.php";
}

require_once __DIR__ . "/includes/footer.php";
?>
