<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/crud.php';

$user = require_login();
$pageTitle = 'My Account';
$error = null;

$account = db_one(
    'SELECT UserID, Username, Email, FirstName, LastName,
            ContactInformation, Role, AccountStatus
     FROM users WHERE UserID = ?',
    [(int)$user['UserID']]
);

if (!$account) {
    http_response_code(404);
    exit('Account not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        if (user_has_history((int)$account['UserID'])) {
            db_execute(
                "UPDATE users SET AccountStatus = 'Inactive'
                 WHERE UserID = ?",
                [$account['UserID']]
            );
        } else {
            db_execute(
                'DELETE FROM users WHERE UserID = ?',
                [$account['UserID']]
            );
        }

        logout_user();
        redirect('index.php');

    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <h1 class="h3 mb-4">My Account</h1>

        <?php show_error($error); ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <dl class="row mb-0">
                    <dt class="col-sm-4">Full Name</dt>
                    <dd class="col-sm-8">
                        <?= e($account['FirstName'] . ' ' . $account['LastName']) ?>
                    </dd>

                    <dt class="col-sm-4">Username</dt>
                    <dd class="col-sm-8">
                        <?= e($account['Username']) ?>
                    </dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">
                        <?= e($account['Email']) ?>
                    </dd>

                    <dt class="col-sm-4">Contact Information</dt>
                    <dd class="col-sm-8">
                        <?= e($account['ContactInformation'] ?: 'Not provided') ?>
                    </dd>
                </dl>

            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">

            <a class="btn btn-primary"
               href="update-user.php?user_id=<?= (int)$account['UserID'] ?>">
                Update Account
            </a>

            <a class="btn btn-outline-primary"
               href="manage-tickets.php">
                My Tickets
            </a>

            <form method="post"
                  onsubmit="return confirm('Delete or deactivate your account?');">

                <?php csrf_field(); ?>

                <button class="btn btn-outline-danger" type="submit">
                    Delete Account
                </button>

            </form>

        </div>

        <p class="text-muted small mt-3">
            Deleting an account requires confirmation.
            Accounts with ticket or transaction history are deactivated
            instead of permanently deleted.
        </p>

    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
