
<?php
/*
 * WF03 Create User Account (UC 01).
 * Fans open it from Register Here on Login; administrators from Create User on
 * Manage Users (create-user.php?from=admin).
 *
 * Midterm demonstration: User records are stored in PHP sessions.
 * Final project: Replace session storage with database operations.
 */

require_once __DIR__ . "/includes/init.php";

$fromAdmin = ($_GET["from"] ?? "") === "admin";
$returnTo = $fromAdmin ? "manage-users.php" : "login.php";

$error = "";

// Process account creation.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $contact = trim($_POST["contact"] ?? "");

    // Validate required fields and input formats.
    if (
        $fullName === "" ||
        !preg_match('/^[A-Za-z0-9_]{3,30}$/', $username) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $error = "Please enter valid information in all required fields.";
    }

    // Check whether username or email is already in use.
    if ($error === "") {
        foreach ($_SESSION["demo_users"] as $existingUser) {
            if (
                strcasecmp($existingUser["username"], $username) === 0 ||
                strcasecmp($existingUser["email"], $email) === 0
            ) {
                $error = "Username or email is already in use.";
                break;
            }
        }
    }

    // Create the demonstration account if validation passes.
    if ($error === "") {

        // Generate a new ID without reusing deleted IDs in this session.
        $newUserId = $_SESSION["demo_next_user_id"]
            ?? (max(array_keys($_SESSION["demo_users"]) ?: [0]) + 1);

        $_SESSION["demo_next_user_id"] = $newUserId + 1;

        // Save account details in the PHP session.
        $_SESSION["demo_users"][$newUserId] = [
            "full_name" => $fullName,
            "username" => $username,
            "email" => $email,
            "contact" => $contact
        ];

        // Return to the appropriate page with a success message.
        redirect(
            $returnTo . "?msg=" .
            ($fromAdmin ? "user_created" : "account_created")
        );
    }
}

$pageTitle = "Create User Account";
$pageRole = $fromAdmin ? "admin" : null;
$navActive = $fromAdmin ? "manage-users.php" : "login.php";

require_once __DIR__ . "/includes/header.php";
?>

<div class="fcb-panel fcb-form mx-auto p-4 p-md-5">

    <h1 class="h3 fw-bold text-center mb-4">
        Create User Account
    </h1>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post"
          action="create-user.php<?= $fromAdmin ? "?from=admin" : "" ?>">

        <!-- Full Name -->
        <div class="mb-3">
            <label for="full_name" class="form-label">
                Full Name<span class="required-mark">*</span>
            </label>

            <input
                type="text"
                class="form-control"
                id="full_name"
                name="full_name"
                maxlength="100"
                value="<?= e($fullName ?? "") ?>"
                autocomplete="name"
                required
            >
        </div>

        <!-- Username -->
        <div class="mb-3">
            <label for="username" class="form-label">
                Username<span class="required-mark">*</span>
            </label>

            <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                minlength="3"
                maxlength="30"
                pattern="[A-Za-z0-9_]+"
                value="<?= e($username ?? "") ?>"
                autocomplete="username"
                aria-describedby="usernameHelp"
                required
            >

            <div id="usernameHelp" class="form-text">
                3 to 30 letters, numbers, or underscores.
            </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">
                Email<span class="required-mark">*</span>
            </label>

            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                maxlength="254"
                value="<?= e($email ?? "") ?>"
                autocomplete="email"
                required
            >
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">
                Password<span class="required-mark">*</span>
            </label>

            <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                aria-describedby="passwordHelp"
                required
            >

            <div id="passwordHelp" class="form-text">
                At least 8 characters.
            </div>
        </div>

        <!-- Contact Information -->
        <div class="mb-3">
            <label for="contact" class="form-label">
                Contact Information
            </label>

            <input
                type="tel"
                class="form-control"
                id="contact"
                name="contact"
                maxlength="30"
                value="<?= e($contact ?? "") ?>"
                autocomplete="tel"
                placeholder="Phone number"
            >
        </div>

        <p class="small text-body-secondary">
            <span class="required-mark">*</span> Required Fields
        </p>

        <!-- Form Actions -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                Create Account
            </button>

            <a class="btn btn-outline-secondary"
               href="<?= e($returnTo) ?>">
                Cancel
            </a>
        </div>

    </form>

    <?php if (!$fromAdmin): ?>
        <p class="text-center mt-4 mb-0">
            Already have an account?
            <a href="login.php">Login Here</a>
        </p>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
