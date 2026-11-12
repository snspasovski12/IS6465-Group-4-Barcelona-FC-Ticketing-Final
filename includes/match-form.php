<?php
/*
 * match-form.php: the fields shared by Create Match (WF06) and Update Match (WF08).
 * Set $match (empty values for Create) and $submitLabel before including.
 */
$today = (new DateTimeImmutable("today"))->format("Y-m-d");
?>
<div class="mb-3">
    <label for="opponent" class="form-label">Opponent Team<span class="required-mark">*</span></label>
    <input type="text" class="form-control" id="opponent" name="opponent" maxlength="100"
        value="<?= e($match["opponent"]) ?>" required>
</div>
<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="match_date" class="form-label">Match Date<span class="required-mark">*</span></label>
        <input type="date" class="form-control" id="match_date" name="match_date" min="<?= e($today) ?>"
            value="<?= e($match["date"]) ?>" aria-describedby="dateHelp" required>
        <div id="dateHelp" class="form-text">Today or later.</div>
    </div>
    <div class="col-sm-6 mb-3">
        <label for="match_time" class="form-label">Match Time<span class="required-mark">*</span></label>
        <input type="time" class="form-control" id="match_time" name="match_time" value="<?= e($match["time"]) ?>"
            required>
    </div>
</div>
<div class="mb-3">
    <label for="match_type" class="form-label">Match Type<span class="required-mark">*</span></label>
    <select class="form-select" id="match_type" name="match_type" required>
        <option value="" <?= $match["type"] === "" ? "selected" : "" ?> disabled>Choose League or Cup</option>
        <?php foreach ($matchTypes as $type): ?>
            <option value="<?= e($type) ?>" <?= $match["type"] === $type ? "selected" : "" ?>><?= e($type) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<p class="small text-body-secondary"><span class="required-mark">*</span> Required Fields</p>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    <a class="btn btn-outline-secondary" href="manage-matches.php">Cancel</a>
</div>
