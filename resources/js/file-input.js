const initializeFileInput = root => {
    const trigger = root.querySelector('.file-input');
    const input = root.querySelector('.file-input__native');
    const placeholder = root.querySelector('[data-file-placeholder]');
    const clear = root.querySelector('[data-file-clear]');
    const live = root.querySelector('[data-file-live]');
    if (!trigger || !input || !placeholder || !clear) return;

    const emptyText = placeholder.textContent;
    const update = () => {
        const file = input.files?.[0];
        placeholder.textContent = file?.name ?? emptyText;
        placeholder.classList.toggle('file-input__filename', Boolean(file));
        clear.hidden = !file;
        if (file && live) live.textContent = `1 archivo seleccionado: ${file.name}`;
    };

    trigger.addEventListener('click', event => {
        if (event.target.closest('[data-file-clear]')) return;
        input.click();
    });
    trigger.addEventListener('keydown', event => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        event.preventDefault();
        input.click();
    });
    input.addEventListener('change', update);
    clear.addEventListener('click', event => {
        event.stopPropagation();
        input.value = '';
        update();
        input.focus();
        if (live) live.textContent = 'Archivo eliminado';
    });
    for (const eventName of ['dragenter', 'dragover']) {
        trigger.addEventListener(eventName, event => {
            event.preventDefault();
            trigger.dataset.dragOver = 'true';
        });
    }
    trigger.addEventListener('dragleave', event => {
        if (event.relatedTarget instanceof Node && trigger.contains(event.relatedTarget)) return;
        trigger.dataset.dragOver = 'false';
    });
    trigger.addEventListener('drop', event => {
        event.preventDefault();
        trigger.dataset.dragOver = 'false';
        if (!event.dataTransfer?.files.length) return;
        const transfer = new DataTransfer();
        transfer.items.add(event.dataTransfer.files[0]);
        input.files = transfer.files;
        update();
    });
};

document.querySelectorAll('[data-file-input]').forEach(initializeFileInput);

document.querySelectorAll('[data-upload-form]').forEach(form => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        const label = button?.querySelector('.button__label');
        if (!button || !label) return;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        label.textContent = 'Cargando…';
        const live = button.querySelector('[role="status"]');
        if (live) live.textContent = 'Cargando archivo';
    });
});
