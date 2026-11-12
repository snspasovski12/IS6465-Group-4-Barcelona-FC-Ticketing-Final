<?php
/*
 * WF02 Login. Front-end prototype: no database yet, so any username with a
 * password of 8 or more characters signs in. The username "admin" opens the
 * administrator pages. The final project will check the USER table and
 * password_verify() instead.
 */
require_once __DIR__ . "/includes/init.php";

$loginValue = "";
$loginFailed = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginValue = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($loginValue !== "" && strlen($password) >= 8) {
        session_regenerate_id(true);
        $_SESSION["role"] = strtolower($loginValue) === "admin" ? "admin" : "user";
        // Successful admin -> Manage Matches; successful user -> Home (WF02)
        redirect($_SESSION["role"] === "admin" ? "manage-matches.php" : "index.php");
    }
    $loginFailed = true;
}

$pageTitle = "Login";
require_once __DIR__ . "/includes/header.php";
?>
<div class="fcb-panel fcb-narrow mx-auto p-4 p-md-5">
    <h1 class="h3 fw-bold text-center mb-4">Login to Your Account</h1>
    <?php require __DIR__ . "/includes/messages.php"; ?>

    <?php if ($loginFailed): ?>
        <div class="alert alert-danger" role="alert">
            <strong>!</strong> Invalid username/email or password.
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <div class="mb-3">
            <label for="login" class="form-label">Username/Email</label>
            <input type="text" class="form-control" id="login" name="login" value="<?= e($loginValue) ?>"
                autocomplete="username" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password"
                autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <p class="text-center mt-4 mb-1">Don't have an account? <a href="create-user.php">Register Here</a></p>
    <p class="text-center small text-body-secondary mb-0">Demo: any username and a password of 8+ characters.
        Use the username <code>admin</code> to see the administrator pages.</p>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
