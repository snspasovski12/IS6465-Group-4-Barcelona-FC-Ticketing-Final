<?php
require_once __DIR__ . '/includes/ticketing.php';
require_once __DIR__ . '/includes/crud.php';

$user = require_login();
$admin = is_admin();

$matchId = (int)($_GET['match_id'] ?? $_POST['match_id'] ?? 0);
$error = '';

/*
 * Administrator ticket creation:
 * - Choose a match and fan.
 * - Reserve an available seat for that fan.
 *
 * Fan reservation:
 * - MatchID comes from WF07.
 * - Ticket belongs to the logged-in fan.
 */
$availableMatches = [];

if ($admin) {
    $allMatches = db_all(
        "SELECT *
         FROM matches
         WHERE MatchStatus = 'Scheduled'
         ORDER BY Date, Time"
    );

    foreach ($allMatches as $candidate) {
        $kickoff = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $candidate['Date'] . ' ' . $candidate['Time'],
            new DateTimeZone('America/Denver')
        );

        if ($kickoff && $kickoff->getTimestamp() > time()) {
            $availableMatches[] = $candidate;
        }
    }
}

$match = $matchId > 0
    ? db_one('SELECT * FROM matches WHERE MatchID = ?', [$matchId])
    : null;

if (!$admin && !$match) {
    http_response_code(404);
    exit('Match not found.');
}

if ($admin && $matchId > 0 && !$match) {
    http_response_code(404);
    exit('Match not found.');
}

$fans = $admin
    ? db_all(
        "SELECT UserID, Username, FirstName, LastName
         FROM users
         WHERE Role = 'Fan' AND AccountStatus = 'Active'
         ORDER BY Username"
    )
    : [];

$selectedFanId = $admin
    ? (int)($_POST['fan_id'] ?? 0)
    : (int)$user['UserID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        if (!$match) {
            throw new RuntimeException('Please select a match.');
        }

        $seatId = (int)($_POST['seat_id'] ?? 0);

        if ($seatId < 1) {
            throw new RuntimeException('Please select an available seat.');
        }

        if ($admin) {
            $selectedFan = db_one(
                "SELECT UserID
                 FROM users
                 WHERE UserID = ?
                   AND Role = 'Fan'
                   AND AccountStatus = 'Active'",
                [$selectedFanId]
            );

            if (!$selectedFan) {
                throw new RuntimeException('Please select an active fan.');
            }

            $ticketOwnerId = (int)$selectedFan['UserID'];
        } else {
            $ticketOwnerId = (int)$user['UserID'];
        }

        $ticketId = ticket_reserve(
            $ticketOwnerId,
            $matchId,
            $seatId
        );

        if ($admin) {
            redirect('manage-tickets.php?created=1');
        }

        redirect('checkout.php?ticket_id=' . $ticketId);

    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

/*
 * Display only seats available for the selected match.
 * ticket_reserve() rechecks availability before saving.
 */
ticket_expire();

$seats = [];

if ($match) {
    $allSeats = db_all(
        "SELECT *
         FROM seats
         WHERE SeatStatus = 'Active'
         ORDER BY Section, Row, Number"
    );

    foreach ($allSeats as $seat) {
        if (ticket_available($matchId, (int)$seat['SeatID'])) {
            $seats[] = $seat;
        }
    }
}

$pageTitle = 'Reserve Seat';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">

        <h1 class="h3 mb-4">Reserve a Seat</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($admin): ?>
            <p class="text-muted">
                Create a Pending ticket for an active fan.
                The fan must complete checkout before the
                15-minute reservation expires.
            </p>
        <?php endif; ?>

        <form method="post" action="reserve-seat.php">

            <?php csrf_field(); ?>

            <?php if ($admin): ?>

                <div class="mb-3">
                    <label for="match_id" class="form-label">
                        Select Match
                    </label>

                    <select id="match_id"
                            name="match_id"
                            class="form-select"
                            required
                            onchange="window.location.href='reserve-seat.php?match_id='+encodeURIComponent(this.value)">

                        <option value="">Choose a match</option>

                        <?php foreach ($availableMatches as $option): ?>
                            <option
                                value="<?= (int)$option['MatchID'] ?>"
                                <?= $matchId === (int)$option['MatchID']
                                    ? 'selected'
                                    : '' ?>>
                                <?= e($option['Opponent']) ?>
                                —
                                <?= e(format_date($option['Date'])) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="fan_id" class="form-label">
                        Assign Ticket To
                    </label>

                    <select id="fan_id"
                            name="fan_id"
                            class="form-select"
                            required>

                        <option value="">Choose a fan</option>

                        <?php foreach ($fans as $fan): ?>
                            <option
                                value="<?= (int)$fan['UserID'] ?>"
                                <?= $selectedFanId === (int)$fan['UserID']
                                    ? 'selected'
                                    : '' ?>>
                                <?= e(
                                    $fan['Username'] . ' — ' .
                                    $fan['FirstName'] . ' ' .
                                    $fan['LastName']
                                ) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

            <?php else: ?>

                <input type="hidden"
                       name="match_id"
                       value="<?= (int)$matchId ?>">

            <?php endif; ?>

            <?php if ($match): ?>

                <div class="card mb-4">
                    <div class="card-body">

                        <h2 class="h5">Selected Match</h2>

                        <p class="mb-1">
                            <strong>Opponent:</strong>
                            <?= e($match['Opponent']) ?>
                        </p>

                        <p class="mb-1">
                            <strong>Date:</strong>
                            <?= e(format_date($match['Date'])) ?>
                        </p>

                        <p class="mb-1">
                            <strong>Time:</strong>
                            <?= e(format_time($match['Time'])) ?>
                        </p>

                        <p class="mb-1">
                            <strong>Match Type:</strong>
                            <?= e($match['MatchType']) ?>
                        </p>

                        <p class="mb-0">
                            <strong>Status:</strong>
                            <?= e($match['MatchStatus']) ?>
                        </p>

                    </div>
                </div>

                <div class="mb-3">
                    <label for="seat_id" class="form-label">
                        Available Seat (Section / Row / Number / Price)
                    </label>

                    <select id="seat_id"
                            name="seat_id"
                            class="form-select"
                            required>

                        <option value="">Choose a seat</option>

                        <?php foreach ($seats as $seat): ?>
                            <option value="<?= (int)$seat['SeatID'] ?>">
                                <?= e($seat['Section']) ?> /
                                <?= (int)$seat['Row'] ?> /
                                <?= (int)$seat['Number'] ?>
                                —
                                $<?= number_format((float)$seat['Price'], 2) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <p class="text-muted">
                    Your reservation is held for 15 minutes.
                </p>

                <?php if (count($seats) > 0): ?>
                    <button class="btn btn-primary" type="submit">
                        Reserve Seat
                    </button>
                <?php else: ?>
                    <div class="alert alert-info">
                        No seats are currently available for this match.
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <a class="btn btn-outline-secondary ms-2"
               href="<?= $admin ? 'manage-tickets.php' : 'matches.php' ?>">
                Cancel
            </a>

        </form>

    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
