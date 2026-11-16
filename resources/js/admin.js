const variantEditor = document.querySelector('[data-variant-editor]');

if (variantEditor) {
    const list = variantEditor.querySelector('[data-variant-list]');
    const template = variantEditor.querySelector('[data-variant-template]');
    const addButton = variantEditor.querySelector('[data-add-variant]');

    const refreshRows = () => {
        const rows = [...list.querySelectorAll('[data-variant-row]')];

        for (const [index, row] of rows.entries()) {
            for (const field of row.querySelectorAll('[data-variant-field]')) {
                field.name = 'variants[' + index + '][' + field.dataset.variantField + ']';
            }

            row.querySelector('[data-remove-variant]').disabled = rows.length === 1;
        }

        addButton.disabled = rows.length >= 30;
    };

    addButton.addEventListener('click', () => {
        const row = template.content.firstElementChild.cloneNode(true);
        list.append(row);
        refreshRows();
        row.querySelector('[data-variant-field="name"]').focus();
    });

    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-variant]');

        if (button && !button.disabled) {
            const row = button.closest('[data-variant-row]');
            const next = row.nextElementSibling ?? row.previousElementSibling;
            row.remove();
            refreshRows();
            next?.querySelector('[data-variant-field="name"]').focus();
        }
    });

    refreshRows();
}

for (const form of document.querySelectorAll('form[data-confirm]')) {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
}

