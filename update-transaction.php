<?php
require_once __DIR__ . '/includes/ticketing.php';

require_admin();

$id = (int)($_GET['transaction_id'] ?? $_POST['transaction_id'] ?? 0);

$tx = db_one(
    'SELECT
        x.*,
        m.Opponent,
        u.Username
     FROM transactions x
     JOIN tickets t ON t.TicketID = x.TicketID
     JOIN matches m ON m.MatchID = t.MatchID
     JOIN users u ON u.UserID = x.UserID
     WHERE x.TransactionID = ?
       AND x.DeletedAt IS NULL',
    [$id]
);

if (!$tx) {
    http_response_code(404);
    exit('Transaction not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        if (($_POST['void'] ?? '') !== '1') {
            throw new RuntimeException(
                'Select Void Transaction to make a change.'
            );
        }

        ticket_void($id);

        redirect('manage-transactions.php');

    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$pageTitle = 'Update Transaction';

require __DIR__ . '/includes/header.php';
?>

<div class="container py-3">

    <h1 class="h3 text-center mb-4">Update Transaction</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">

        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <h2 class="h5 mb-3">Transaction Information</h2>

                    <dl class="row">

                        <dt class="col-5">Transaction ID</dt>
                        <dd class="col-7">
                            <?= e(transaction_id_label($id)) ?>
                        </dd>

                        <dt class="col-5">Ticket ID</dt>
                        <dd class="col-7">
                            <?= e(ticket_id_label((int)$tx['TicketID'])) ?>
                        </dd>

                        <dt class="col-5">Match</dt>
                        <dd class="col-7">
                            <?= e($tx['Opponent']) ?>
                        </dd>

                        <dt class="col-5">User</dt>
                        <dd class="col-7">
                            <?= e($tx['Username']) ?>
                        </dd>

                        <dt class="col-5">Amount</dt>
                        <dd class="col-7">
                            $<?= number_format((float)$tx['Amount'], 2) ?>
                        </dd>

                        <dt class="col-5">Payment Method</dt>
                        <dd class="col-7">
                            <?= e($tx['PaymentMethod']) ?>
                        </dd>

                        <dt class="col-5">Current Status</dt>
                        <dd class="col-7">
                            <?= e($tx['TransactionStatus']) ?>
                        </dd>

                        <dt class="col-5">Transaction Date</dt>
                        <dd class="col-7">
                            <?= e($tx['TransactionDate']) ?> UTC
                        </dd>

                    </dl>

                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <h2 class="h5 mb-3">Update Transaction</h2>

                    <label for="transaction-status" class="form-label">
                        Transaction Status
                    </label>

                    <input
                        id="transaction-status"
                        class="form-control mb-3"
                        type="text"
                        value="<?= e($tx['TransactionStatus']) ?>"
                        readonly
                    >

                    <p class="text-muted small">
                        Current status is read-only.
                        Administrators cannot arbitrarily modify
                        transaction history.
                    </p>

                    <?php if ($tx['TransactionStatus'] === 'Completed'): ?>

                        <form method="post" id="void-transaction-form">

                            <input type="hidden"
                                   name="csrf_token"
                                   value="<?= e(csrf_token()) ?>">

                            <input type="hidden"
                                   name="transaction_id"
                                   value="<?= (int)$id ?>">

                            <div class="form-check mt-3">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="void"
                                    value="1"
                                    id="void"
                                    required
                                >

                                <label class="form-check-label" for="void">
                                    Void Transaction
                                </label>

                            </div>

                            <p class="text-muted small mt-2">
                                Voiding this Completed transaction will
                                change its status to Voided and cancel
                                the associated Purchased ticket.
                                Both records are preserved.
                            </p>

                        </form>

                    <?php else: ?>

                        <div class="alert alert-secondary">
                            Only Completed transactions can be voided.
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-center gap-2 mt-4">

        <?php if ($tx['TransactionStatus'] === 'Completed'): ?>

            <button
                type="submit"
                form="void-transaction-form"
                class="btn btn-danger"
            >
                Update Transaction
            </button>

        <?php endif; ?>

        <a href="manage-transactions.php"
           class="btn btn-outline-secondary">
            Cancel
        </a>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
