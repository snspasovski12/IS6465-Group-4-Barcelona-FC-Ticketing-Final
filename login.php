<?php
require_once __DIR__ . '/includes/init.php';

if (current_user()) {
    redirect(is_admin() ? 'manage-matches.php' : 'index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (login_user($username, $password)) {
        redirect(is_admin() ? 'manage-matches.php' : 'index.php');
    }

    $error = 'Invalid username/email or password.';
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 mb-4 text-center">Login to Your Account</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <div class="mb-3">
                <label for="username" class="form-label">
                    Username / Email
                </label>
                <input id="username" name="username"
                       class="form-control" required autocomplete="username">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password"
                       class="form-control" required
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Login
            </button>
        </form>

        <p class="text-center mt-3">
            Don't have an account?
            <a href="create-user.php">Register Here</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
