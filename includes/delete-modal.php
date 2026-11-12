<?php
/*
 * delete-modal.php: the "Delete requires confirmation" prompt (WF04, WF04A, WF07A).
 * Set before including:
 *   $modalTitle   e.g. "Delete user account?"
 *   $modalBody    text before the record name, e.g. "Delete the account"
 *   $modalField   hidden input name, e.g. "user_id"
 *   $modalAction  page the form posts to
 * A Delete button opens it with data-bs-toggle="modal" data-bs-target="#deleteModal"
 * plus data-record-id and data-record-name; js/app.js copies those into the form.
 * "Cancel Delete" closes the prompt and leaves the record unchanged.
 */
?>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="<?= e($modalAction) ?>">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="deleteModalTitle"><?= e($modalTitle) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0"><?= e($modalBody) ?> <strong data-record-name><?= e($modalDefaultName ?? "") ?></strong>?
                    This cannot be undone.</p>
                <input type="hidden" name="<?= e($modalField) ?>" value="<?= e((string) ($modalDefaultId ?? "")) ?>" data-record-id>
                <input type="hidden" name="action" value="delete">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel Delete</button>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>
