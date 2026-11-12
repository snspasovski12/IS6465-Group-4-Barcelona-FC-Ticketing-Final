<?php
/*
 * WF05 Update User Account (UC 03), shared by fans and administrators.
 *   Fan:   update-user.php?user_id=1            -> returns to My Account
 *   Admin: update-user.php?user_id=2&from=admin -> returns to Manage Users
 */
require_once __DIR__ . "/includes/init.php";
$fromAdmin = ($_GET["from"] ?? "") === "admin";
$userId = id_from_url("user_id") ?? ($fromAdmin ? null : $currentUserId);
$user = $userId !== null ? ($users[$userId] ?? null) : null;
$returnTo = $fromAdmin ? "manage-users.php" : "my-account.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $user !== null) {
    $fullName = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contact = trim($_POST["contact"] ?? "");

    if (
        $fullName === "" ||
        $username === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $error = "Please enter valid information in all required fields.";
    } else {
        $_SESSION["demo_users"][$userId] = [
            "full_name" => $fullName,
            "username" => $username,
            "email" => $email,
            "contact" => $contact
        ];

        redirect($returnTo . "?msg=" . ($fromAdmin ? "user_updated" : "account_updated"));
    }
}

$pageTitle = "Update User Account";
$pageRole = $fromAdmin ? "admin" : "user";
$navActive = $returnTo;
require_once __DIR__ . "/includes/header.php";
?>
<div class="fcb-panel fcb-form mx-auto p-4 p-md-5">
    <h1 class="h3 fw-bold text-center mb-4">Update User Account</h1>
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= e($error) ?>
    </div>
<?php endif; ?>

    <?php if ($user === null): ?>
        <div class="alert alert-warning" role="alert">User not found. Choose an account from the list.</div>
        <a class="btn btn-outline-secondary" href="<?= e($returnTo) ?>">Back</a>
    <?php else: ?>
        <form method="post" action="update-user.php?user_id=<?= $userId ?><?= $fromAdmin ? "&amp;from=admin" : "" ?>">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name<span class="required-mark">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" maxlength="100"
                    value="<?= e($user["full_name"]) ?>" autocomplete="name" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username<span class="required-mark">*</span></label>
                <input type="text" class="form-control" id="username" name="username" minlength="3" maxlength="30"
                    pattern="[A-Za-z0-9_]+" value="<?= e($user["username"]) ?>" autocomplete="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email<span class="required-mark">*</span></label>
                <input type="email" class="form-control" id="email" name="email" maxlength="254"
                    value="<?= e($user["email"]) ?>" autocomplete="email" required>
            </div>
            <div class="mb-3">
                <label for="contact" class="form-label">Contact Information</label>
                <input type="tel" class="form-control" id="contact" name="contact" maxlength="30"
                    value="<?= e($user["contact"]) ?>" autocomplete="tel">
            </div>
            <p class="small text-body-secondary"><span class="required-mark">*</span> Required Fields</p>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Account</button>
                <a class="btn btn-outline-secondary" href="<?= e($returnTo) ?>">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
