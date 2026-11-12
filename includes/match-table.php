<?php
/*
 * match-table.php: the match list shared by View Matches (WF07) and
 * Manage Matches (WF07A). Set $showActions = true for the admin Edit | Delete column.
 */
$showActions = $showActions ?? false;
?>
<?php if (count($matches) === 0): ?>
    <p class="alert alert-info mb-0">There are no upcoming matches.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col">Opponent</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                    <th scope="col">Match Type</th>
                    <?php if ($showActions): ?><th scope="col" class="text-end">Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matches as $id => $match): ?>
                    <tr>
                        <td><?= e($match["opponent"]) ?></td>
                        <td><?= e(format_date($match["date"])) ?></td>
                        <td><?= e(format_time($match["time"])) ?></td>
                        <td><span class="badge badge-<?= strtolower(e($match["type"])) ?>"><?= e($match["type"]) ?></span></td>
                        <?php if ($showActions): ?>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="update-match.php?match_id=<?= $id ?>">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-record-id="<?= $id ?>"
                                    data-record-name="<?= e($match["opponent"] . " on " . format_date($match["date"])) ?>">Delete</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
