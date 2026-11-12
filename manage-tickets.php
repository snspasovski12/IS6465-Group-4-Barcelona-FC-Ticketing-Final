<?php
require_once __DIR__ . '/includes/ticketing.php';

$user = require_login();
$admin = is_admin();

ticket_expire();

/*
 * Administrator-only Delete action.
 *
 * Pending tickets are cancelled rather than physically deleted.
 * All other statuses remain preserved for transaction history.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    verify_csrf();

    $ticketId = filter_var(
        $_POST['ticket_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$ticketId) {
        http_response_code(400);
        exit('Invalid ticket ID.');
    }

    $record = db_one(
        'SELECT TicketStatus FROM tickets WHERE TicketID = ?',
        [$ticketId]
    );

    if (!$record) {
        http_response_code(404);
        exit('Ticket not found.');
    }

    if ($record['TicketStatus'] === 'Pending') {
        db_execute(
            "UPDATE tickets
             SET TicketStatus = 'Cancelled'
             WHERE TicketID = ? AND TicketStatus = 'Pending'",
            [$ticketId]
        );
        redirect('manage-tickets.php?cancelled=1');
    }

    redirect('manage-tickets.php?preserved=1');
}

/*
 * Filters are available only to administrators.
 */
$matchFilter = $admin
    ? trim((string)($_GET['match'] ?? ''))
    : '';

$statusFilter = $admin
    ? trim((string)($_GET['status'] ?? ''))
    : '';

$allowedStatuses = [
    'Pending',
    'Purchased',
    'Cancelled',
    'Expired'
];

if (!in_array($statusFilter, array_merge([''], $allowedStatuses), true)) {
    $statusFilter = '';
}

$matches = $admin
    ? db_all(
        'SELECT MatchID, Opponent, Date
         FROM matches
         ORDER BY Date DESC, Opponent'
    )
    : [];

/*
 * Retrieve tickets and related records.
 */
$sql = '
    SELECT
        t.*,
        u.Username,
        m.Opponent,
        s.Section,
        s.Row,
        s.Number
    FROM tickets t
    JOIN users u ON u.UserID = t.UserID
    JOIN matches m ON m.MatchID = t.MatchID
    JOIN seats s ON s.SeatID = t.SeatID
    WHERE 1 = 1
';

$params = [];

if (!$admin) {
    $sql .= ' AND t.UserID = ?';
    $params[] = (int)$user['UserID'];
}

if ($admin && $matchFilter !== '') {
    $sql .= ' AND t.MatchID = ?';
    $params[] = $matchFilter;
}

if ($admin && $statusFilter !== '') {
    $sql .= ' AND t.TicketStatus = ?';
    $params[] = $statusFilter;
}

$sql .= ' ORDER BY t.TicketID DESC';

$rows = db_all($sql, $params);

$pageTitle = $admin ? 'Manage Tickets' : 'My Tickets';

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-4"><?= e($pageTitle) ?></h1>

<?php if (isset($_GET['paid'])): ?>
    <div class="alert alert-success" role="status">
        Simulated purchase completed.
    </div>
<?php endif; ?>

<?php if (isset($_GET['cancelled'])): ?>
    <div class="alert alert-success" role="status">
        Pending ticket cancelled successfully.
        Ticket history has been preserved.
    </div>
<?php endif; ?>

<?php if (isset($_GET['preserved'])): ?>
    <div class="alert alert-info" role="status">
        This ticket cannot be deleted or cancelled here.
        Historical records are preserved.
        Purchased tickets must be voided through
        transaction management.
    </div>
<?php endif; ?>

<?php if ($admin): ?>

    <section class="card shadow-sm mb-4">
        <div class="card-body">

            <h2 class="h5 mb-3">Search / Filter</h2>

            <form method="get" action="manage-tickets.php">

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">

                        <label for="match" class="form-label">
                            Match
                        </label>

                        <select id="match"
                                name="match"
                                class="form-select">

                            <option value="">All</option>

                            <?php foreach ($matches as $match): ?>
                                <option
                                    value="<?= (int)$match['MatchID'] ?>"
                                    <?= $matchFilter === (string)$match['MatchID']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= e($match['Opponent']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label for="status" class="form-label">
                            Ticket Status
                        </label>

                        <select id="status"
                                name="status"
                                class="form-select">

                            <option value="">All</option>

                            <?php foreach ($allowedStatuses as $status): ?>
                                <option
                                    value="<?= e($status) ?>"
                                    <?= $statusFilter === $status
                                        ? 'selected'
                                        : '' ?>>
                                    <?= e($status) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <button type="submit"
                                class="btn btn-outline-primary">
                            Search
                        </button>

                        <a href="manage-tickets.php"
                           class="btn btn-outline-secondary">
                            Clear
                        </a>

                    </div>

                </div>

            </form>

        </div>
    </section>

    <div class="mb-3">
        <a href="reserve-seat.php"
           class="btn btn-primary">
            Add Ticket
        </a>
    </div>

<?php endif; ?>

<h2 class="h5 mb-3">Ticket List</h2>

<div class="table-responsive">

    <table class="table table-striped table-hover align-middle">

        <thead>

            <?php if ($admin): ?>

                <tr>
                    <th>Ticket ID</th>
                    <th>Match</th>
                    <th>Section</th>
                    <th>Row</th>
                    <th>Number</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>

            <?php else: ?>

                <tr>
                    <th>Ticket</th>
                    <th>Match</th>
                    <th>Seat</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Checkout</th>
                </tr>

            <?php endif; ?>

        </thead>

        <tbody>

            <?php foreach ($rows as $ticket): ?>

                <tr>

                    <td>
                        <?= e(ticket_id_label((int)$ticket['TicketID'])) ?>
                    </td>

                    <td><?= e($ticket['Opponent']) ?></td>

                    <?php if ($admin): ?>

                        <td><?= e($ticket['Section']) ?></td>
                        <td><?= (int)$ticket['Row'] ?></td>
                        <td><?= (int)$ticket['Number'] ?></td>

                    <?php else: ?>

                        <td>
                            <?= e($ticket['Section']) ?>/
                            <?= (int)$ticket['Row'] ?>/
                            <?= (int)$ticket['Number'] ?>
                        </td>

                    <?php endif; ?>

                    <td>
                        $<?= number_format(
                            (float)$ticket['TicketPrice'],
                            2
                        ) ?>
                    </td>

                    <td><?= e($ticket['TicketStatus']) ?></td>

                    <?php if (!$admin): ?>
                        <td>
                            <?php if (
                                $ticket['TicketStatus'] === 'Pending'
                                && !empty($ticket['ExpiresAt'])
                                && $ticket['ExpiresAt'] > ticket_now()
                            ): ?>
                                <a class="btn btn-sm btn-primary"
                                   href="checkout.php?ticket_id=<?= (int)$ticket['TicketID'] ?>">
                                    Checkout
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($admin): ?>

                        <td class="text-nowrap">

                            <a class="btn btn-sm btn-outline-primary"
                               href="update-ticket.php?ticket_id=<?= (int)$ticket['TicketID'] ?>">
                                Edit
                            </a>

                            <form method="post"
                                  class="d-inline"
                                  onsubmit="return confirm('Cancel this Pending ticket? Historical tickets cannot be deleted.');">

                                <input type="hidden"
                                       name="csrf_token"
                                       value="<?= e(csrf_token()) ?>">

                                <input type="hidden"
                                       name="ticket_id"
                                       value="<?= (int)$ticket['TicketID'] ?>">

                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger">
                                    Delete
                                </button>

                            </form>

                        </td>

                    <?php endif; ?>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php if (count($rows) === 0): ?>
    <div class="alert alert-info">
        No tickets match the selected filters.
    </div>
<?php endif; ?>

<div class="mt-3">

    <?php if ($admin): ?>

        <a href="manage-matches.php"
           class="btn btn-outline-secondary">
            Manage Matches
        </a>

    <?php else: ?>

        <a href="my-account.php"
           class="btn btn-outline-secondary">
            My Account
        </a>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
