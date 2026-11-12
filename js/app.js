/*
 * app.js: fills the delete confirmation prompt with the record the admin clicked.
 * Each Delete button carries data-record-id and data-record-name.
 */
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("deleteModal");
    if (!modal) return;

    modal.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        if (!button || !button.dataset.recordId) return;
        modal.querySelector("[data-record-id]").value = button.dataset.recordId;
        modal.querySelector("[data-record-name]").textContent = button.dataset.recordName;
    });
});
