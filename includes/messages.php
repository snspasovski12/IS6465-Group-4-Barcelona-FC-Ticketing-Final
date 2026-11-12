<?php
/*
 * messages.php: shows a success message after an action.
 * Pages redirect with ?msg=<key>; only keys in this list are displayed,
 * so a visitor cannot inject their own text through the URL.
 */
$messageText = [
    "account_created" => "Your account was created. Please log in.",
    "account_updated" => "Your account was updated.",
    "account_deleted" => "Your account was deleted.",
    "user_created" => "The user account was created.",
    "user_updated" => "The user account was updated.",
    "user_deleted" => "The user account was deleted.",
    "user_delete_blocked" => "Account cannot be deleted because it has associated tickets or transactions.",
    "match_created" => "The match was created.",
    "match_updated" => "The match was updated.",
    "match_deleted" => "The match was deleted.",
    "logged_out" => "You have been logged out.",
];

$errorMessages = ["user_delete_blocked"];

$msgKey = $_GET["msg"] ?? "";
if (is_string($msgKey) && isset($messageText[$msgKey])): ?>
    <div class="alert alert-<?= in_array($msgKey, $errorMessages, true) ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
        <?= e($messageText[$msgKey]) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>