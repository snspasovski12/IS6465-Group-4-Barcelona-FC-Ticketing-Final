<?php
require_once __DIR__ . '/includes/ticketing.php';

$user = require_login();
$id = (int)($_GET['ticket_id'] ?? $_POST['ticket_id'] ?? 0);
$ticket = ticket_record($id);

if (!$ticket || (int)$ticket['UserID'] !== (int)$user['UserID']) {
    http_response_code(404);
    exit('Ticket not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'cancel') {
        db_execute(
            "UPDATE tickets SET TicketStatus='Cancelled'
             WHERE TicketID=? AND UserID=? AND TicketStatus='Pending'",
            [$id, $user['UserID']]
        );
        redirect('matches.php');
    }

    if (!in_array($action, ['success', 'failure'], true)) {
        http_response_code(400);
        exit('Invalid checkout action.');
    }

    try {
        ticket_pay($id, (int)$user['UserID'], $action === 'success');

        if ($action === 'success') {
            redirect('manage-tickets.php?paid=1');
        }

        $error = 'Simulated payment failed. Your reservation remains pending until expiration.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }

    $ticket = ticket_record($id);
}

$isPending = $ticket['TicketStatus'] === 'Pending';
$isActive = $isPending
    && !empty($ticket['ExpiresAt'])
    && $ticket['ExpiresAt'] > ticket_now();

$expiresAt = !empty($ticket['ExpiresAt'])
    ? strtotime($ticket['ExpiresAt'] . ' UTC')
    : false;

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-3">

    <h1 class="h3 text-center mb-4">
        Checkout / Create Transaction
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-warning" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">

                    <h2 class="h6">Reservation Time Remaining</h2>

                    <?php if ($isActive && $expiresAt !== false): ?>
                        <div id="reservation-countdown"
                             class="fs-3 fw-bold"
                             role="timer"
                             aria-label="Reservation time remaining"
                             data-expires="<?= (int)$expiresAt * 1000 ?>">
                            Calculating...
                        </div>
                        <p class="text-muted small mb-0">
                            Complete checkout before your reservation expires.
                        </p>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">
                            Reservation is no longer available for checkout.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 justify-content-center">

        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <h2 class="h5 mb-3">Purchase Summary</h2>

                    <dl class="row">

                        <dt class="col-5">Ticket ID</dt>
                        <dd class="col-7">
                            <?= e(ticket_id_label($id)) ?>
                        </dd>

                        <dt class="col-5">Match</dt>
                        <dd class="col-7">
                            <?= e($ticket['Opponent']) ?>
                        </dd>

                        <dt class="col-5">Date</dt>
                        <dd class="col-7">
                            <?= e(format_date($ticket['Date'])) ?>
                        </dd>

                        <dt class="col-5">Time</dt>
                        <dd class="col-7">
                            <?= e(format_time($ticket['Time'])) ?>
                        </dd>

                        <dt class="col-5">Section</dt>
                        <dd class="col-7">
                            <?= e($ticket['Section']) ?>
                        </dd>

                        <dt class="col-5">Row</dt>
                        <dd class="col-7">
                            <?= (int)$ticket['Row'] ?>
                        </dd>

                        <dt class="col-5">Seat Number</dt>
                        <dd class="col-7">
                            <?= (int)$ticket['Number'] ?>
                        </dd>

                        <dt class="col-5">Ticket Price</dt>
                        <dd class="col-7 fw-bold">
                            $<?= number_format((float)$ticket['TicketPrice'], 2) ?>
                        </dd>

                        <dt class="col-5">Status</dt>
                        <dd class="col-7">
                            <?= e($ticket['TicketStatus']) ?>
                        </dd>

                    </dl>

                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <h2 class="h5 mb-3">Payment</h2>

                    <label class="form-label">Payment Method</label>

                    <input class="form-control mb-3"
                           type="text"
                           value="Simulated Payment"
                           readonly>

                    <p class="text-muted small">
                        This is a simulated payment.
                        No real payment information is collected.
                    </p>

                    <p class="small">
                        Your ticket will be marked Purchased only
                        after successful checkout.
                    </p>

                </div>
            </div>
        </div>

    </div>

    <div class="text-center mt-4">

        <?php if ($isActive): ?>

            <form method="post" id="checkout-form"
                  class="d-flex flex-wrap justify-content-center gap-2">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e(csrf_token()) ?>">

                <input type="hidden"
                       name="ticket_id"
                       value="<?= (int)$id ?>">

                <button class="btn btn-primary checkout-action"
                        type="submit"
                        name="action"
                        value="success">
                    Confirm Purchase
                </button>

                <button class="btn btn-outline-warning checkout-action"
                        type="submit"
                        name="action"
                        value="failure">
                    Simulate Failure
                </button>

                <button class="btn btn-outline-secondary"
                        type="submit"
                        name="action"
                        value="cancel">
                    Cancel Purchase
                </button>

            </form>

        <?php else: ?>

            <a href="matches.php" class="btn btn-outline-secondary">
                Back to Matches
            </a>

        <?php endif; ?>

    </div>

</div>

<?php if ($isActive && $expiresAt !== false): ?>
<script>
(() => {
    const display = document.getElementById('reservation-countdown');
    if (!display) return;

    const expiration = Number(display.dataset.expires);
    const checkoutButtons = document.querySelectorAll('.checkout-action');

    function updateCountdown() {
        const remaining = Math.max(0, expiration - Date.now());
        const totalSeconds = Math.ceil(remaining / 1000);

        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        display.textContent =
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');

        if (remaining <= 0) {
            display.textContent = '00:00';
            display.classList.add('text-danger');

            checkoutButtons.forEach(button => {
                button.disabled = true;
            });

            const notice = document.createElement('p');
            notice.className = 'text-danger fw-bold mt-2';
            notice.textContent =
                'Reservation expired. Please select another available seat.';

            if (!document.getElementById('expiration-notice')) {
                notice.id = 'expiration-notice';
                display.after(notice);
            }

            clearInterval(timer);
        }
    }

    const timer = setInterval(updateCountdown, 1000);
    updateCountdown();
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
