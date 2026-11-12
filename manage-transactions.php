<?php
require_once __DIR__ . '/includes/ticketing.php';

$user = require_login();
$admin = is_admin();

/*
 * Delete is a soft delete.
 * The transaction remains in SQLite for historical purposes.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    verify_csrf();

    $id = (int)($_POST['transaction_id'] ?? 0);

    if ($id > 0) {
        db_execute(
            'UPDATE transactions
             SET DeletedAt = ?
             WHERE TransactionID = ?
             AND DeletedAt IS NULL',
            [ticket_now(), $id]
        );
    }

    redirect('manage-transactions.php');
}

/*
 * Filters are available to administrators only.
 * Date values are interpreted as UTC calendar dates.
 */
$matchFilter = $admin
    ? trim((string)($_GET['match'] ?? ''))
    : '';

$statusFilter = $admin
    ? trim((string)($_GET['status'] ?? ''))
    : '';

$dateFrom = $admin
    ? trim((string)($_GET['date_from'] ?? ''))
    : '';

$dateTo = $admin
    ? trim((string)($_GET['date_to'] ?? ''))
    : '';

$allowedStatuses = [
    'Completed',
    'Failed',
    'Cancelled',
    'Voided'
];

if (!in_array($statusFilter, array_merge([''], $allowedStatuses), true)) {
    $statusFilter = '';
}

function valid_transaction_filter_date(string $date): bool
{
    if ($date === '') {
        return true;
    }

    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    return $parsed !== false
        && $parsed->format('Y-m-d') === $date;
}

$filterError = '';

if (!valid_transaction_filter_date($dateFrom)
    || !valid_transaction_filter_date($dateTo)) {
    $filterError = 'Please enter valid dates.';
}

if ($filterError === ''
    && $dateFrom !== ''
    && $dateTo !== ''
    && $dateFrom > $dateTo) {
    $filterError = 'Date From cannot be later than Date To.';
}

/*
 * Retrieve available matches for the dropdown.
 */
$matches = $admin
    ? db_all(
        'SELECT MatchID, Opponent, Date
         FROM matches
         ORDER BY Date DESC, Opponent'
    )
    : [];

/*
 * Retrieve transaction records.
 * All user-provided values are passed as SQL parameters.
 */
$sql = '
    SELECT
        x.*,
        u.Username,
        m.Opponent
    FROM transactions x
    JOIN users u ON u.UserID = x.UserID
    JOIN tickets t ON t.TicketID = x.TicketID
    JOIN matches m ON m.MatchID = t.MatchID
    WHERE x.DeletedAt IS NULL
';

$params = [];

if (!$admin) {
    $sql .= ' AND x.UserID = ?';
    $params[] = (int)$user['UserID'];
}

if ($admin && $filterError === '') {

    if ($matchFilter !== '') {
        $sql .= ' AND m.MatchID = ?';
        $params[] = $matchFilter;
    }

    if ($statusFilter !== '') {
        $sql .= ' AND x.TransactionStatus = ?';
        $params[] = $statusFilter;
    }

    if ($dateFrom !== '') {
        $sql .= ' AND x.TransactionDate >= ?';
        $params[] = $dateFrom . ' 00:00:00';
    }

    if ($dateTo !== '') {
        $sql .= ' AND x.TransactionDate < ?';

        $nextDate = (new DateTimeImmutable(
            $dateTo,
            new DateTimeZone('UTC')
        ))->modify('+1 day')->format('Y-m-d');

        $params[] = $nextDate . ' 00:00:00';
    }
}

$sql .= ' ORDER BY x.TransactionDate DESC, x.TransactionID DESC';

$rows = $filterError === ''
    ? db_all($sql, $params)
    : [];

$pageTitle = $admin
    ? 'Manage Transactions'
    : 'My Transactions';

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-4"><?= e($pageTitle) ?></h1>

<?php if ($filterError !== ''): ?>
    <div class="alert alert-danger" role="alert">
        <?= e($filterError) ?>
    </div>
<?php endif; ?>

<?php if ($admin): ?>

    <section class="card shadow-sm mb-4">
        <div class="card-body">

            <h2 class="h5 mb-3">Search / Filter</h2>

            <form method="get" action="manage-transactions.php">

                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
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

                    <div class="col-md-2">
                        <label for="status" class="form-label">
                            Status
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

                    <div class="col-md-2">
                        <label for="date_from" class="form-label">
                            Date From
                        </label>

                        <input type="date"
                               id="date_from"
                               name="date_from"
                               class="form-control"
                               value="<?= e($dateFrom) ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="date_to" class="form-label">
                            Date To
                        </label>

                        <input type="date"
                               id="date_to"
                               name="date_to"
                               class="form-control"
                               value="<?= e($dateTo) ?>">
                    </div>

                    <div class="col-md-3">
                        <button type="submit"
                                class="btn btn-outline-primary">
                            Search
                        </button>

                        <a href="manage-transactions.php"
                           class="btn btn-outline-secondary">
                            Clear
                        </a>
                    </div>

                </div>

            </form>

        </div>
    </section>

<?php endif; ?>

<h2 class="h5 mb-3">Transaction List</h2>

<div class="table-responsive">

    <table class="table table-striped table-hover align-middle">

        <thead>
            <tr>
                <th scope="col">Transaction ID</th>
                <th scope="col">Ticket ID</th>
                <th scope="col">Match</th>
                <th scope="col">User</th>
                <th scope="col">Amount</th>
                <th scope="col">Method</th>
                <th scope="col">Status</th>
                <th scope="col">Date/Time (UTC)</th>

                <?php if ($admin): ?>
                    <th scope="col">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($rows as $transaction): ?>

                <tr>
                    <td>
                        <?= e(transaction_id_label(
                            (int)$transaction['TransactionID']
                        )) ?>
                    </td>

                    <td>
                        <?= e(ticket_id_label(
                            (int)$transaction['TicketID']
                        )) ?>
                    </td>

                    <td><?= e($transaction['Opponent']) ?></td>

                    <td><?= e($transaction['Username']) ?></td>

                    <td>
                        $<?= number_format(
                            (float)$transaction['Amount'],
                            2
                        ) ?>
                    </td>

                    <td><?= e($transaction['PaymentMethod']) ?></td>

                    <td><?= e($transaction['TransactionStatus']) ?></td>

                    <td><?= e($transaction['TransactionDate']) ?></td>

                    <?php if ($admin): ?>

                        <td class="text-nowrap">

                            <a class="btn btn-sm btn-outline-primary"
                               href="update-transaction.php?transaction_id=<?= (int)$transaction['TransactionID'] ?>">
                                Edit
                            </a>

                            <form method="post"
                                  class="d-inline"
                                  onsubmit="return confirm('Soft-delete this transaction from the list?');">

                                <input type="hidden"
                                       name="csrf_token"
                                       value="<?= e(csrf_token()) ?>">

                                <input type="hidden"
                                       name="transaction_id"
                                       value="<?= (int)$transaction['TransactionID'] ?>">

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
        No transactions match the selected filters.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
