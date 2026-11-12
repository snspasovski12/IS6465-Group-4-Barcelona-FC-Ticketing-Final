<?php
/*
 * WF04A Manage Users (UC 02 for an administrator).
 * Starts UC 01 (Create User), UC 03 (Edit), and UC 04 (Delete).
 */
require_once __DIR__ . "/includes/init.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {

    $deleteUserId = filter_input(
        INPUT_POST,
        "user_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($deleteUserId === false || $deleteUserId === null ||
        !isset($_SESSION["demo_users"][$deleteUserId])) {
        redirect("manage-users.php");
    }

    // Midterm demonstration: User ID 1 has associated tickets/transactions.
    if ($deleteUserId === 1) {
        redirect("manage-users.php?msg=user_delete_blocked");
    }

    // Remove the selected user from the session-backed sample data.
    unset($_SESSION["demo_users"][$deleteUserId]);

    redirect("manage-users.php?msg=user_deleted");
}

$pageTitle = "Manage Users";
$pageRole = "admin";
require_once __DIR__ . "/includes/header.php";
?>
<div class="fcb-panel p-4 p-md-5">
    <h1 class="h3 fw-bold text-center mb-4">Manage User Accounts</h1>
    <?php require __DIR__ . "/includes/messages.php"; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0">User Accounts</h2>
        <a class="btn btn-primary" href="create-user.php?from=admin">Create User</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col">User Name</th>
                    <th scope="col">Full Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Contact Information</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $id => $user): ?>
                    <tr>
                        <td><?= e($user["username"]) ?></td>
                        <td><?= e($user["full_name"]) ?></td>
                        <td><?= e($user["email"]) ?></td>
                        <td><?= e($user["contact"]) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="update-user.php?user_id=<?= $id ?>&amp;from=admin">Edit</a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                data-bs-target="#deleteModal" data-record-id="<?= $id ?>"
                                data-record-name="<?= e($user["username"]) ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="small fst-italic text-body-secondary mb-0">Delete requires confirmation before removing an account.</p>
</div>
<?php
$modalTitle = "Delete user account?";
$modalBody = "Delete the account";
$modalField = "user_id";
$modalAction = "manage-users.php";
require __DIR__ . "/includes/delete-modal.php";
require_once __DIR__ . "/includes/footer.php";
