/*
 * DOM state for the server-rendered settings-sidebar
 * ExpandableRow. Persistence remains ordinary Laravel form submission.
 */
document.addEventListener('click', (event) => {
    const edit = event.target.closest('[data-expandable-row-edit]');
    const cancel = event.target.closest('[data-expandable-row-cancel]');

    if (!edit && !cancel) return;

    event.preventDefault();
    const row = (edit ?? cancel).closest('[data-expandable-row]');
    const summary = row?.querySelector('[data-expandable-row-summary]');
    const editor = row?.querySelector('[data-expandable-row-editor]');
    const editControl = row?.querySelector('[data-expandable-row-edit]');
    if (!row || !summary || !editor) return;

    const isEditing = Boolean(edit);
    row.dataset.expanded = isEditing ? 'true' : 'false';
    summary.hidden = isEditing;
    editor.hidden = !isEditing;

    if (isEditing) {
        editor.querySelector('input:not([type="hidden"]), textarea, select')?.focus();
    } else {
        editControl?.focus();
    }
});
