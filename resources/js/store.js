const product = document.querySelector('[data-store-product]');

if (product) {
    const image = product.querySelector('[data-gallery-image]');
    const thumbnails = [...product.querySelectorAll('[data-gallery-thumbnail]')];

    const galleryControls = product.querySelector('[data-gallery-controls]');
    const galleryDots = product.querySelector('[data-gallery-dots]');
    let currentPhoto = 0;

    const showPhoto = (index) => {
        currentPhoto = (index + thumbnails.length) % thumbnails.length;
        const thumbnail = thumbnails[currentPhoto];
        image.src = thumbnail.dataset.src;
        image.alt = thumbnail.dataset.alt;

        thumbnails.forEach((button, position) => {
            button.setAttribute('aria-pressed', String(position === currentPhoto));
        });
        galleryDots?.querySelectorAll('[data-gallery-dot]').forEach((dot, position) => {
            dot.toggleAttribute('data-current', position === currentPhoto);
        });
    };

    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', () => showPhoto(index));
    });

    if (galleryControls && galleryDots) {
        galleryControls.hidden = false;
        galleryDots.hidden = false;
        for (const button of galleryControls.querySelectorAll('[data-gallery-step]')) {
            button.addEventListener('click', () => showPhoto(currentPhoto + Number(button.dataset.galleryStep)));
        }
    }

    const variants = product.querySelector('[data-product-variant]');
    const price = product.querySelector('[data-product-price]');
    const stock = product.querySelector('[data-product-stock]');
    const initialStock = stock.textContent.trim();
    const quantity = product.querySelector('[data-product-quantity]');
    const format = new Intl.NumberFormat('id-ID');

    const addButton = product.querySelector('[data-product-add]');
    const variantButtons = [...product.querySelectorAll('[data-variant-choice]')];
    const steps = [...product.querySelectorAll('[data-quantity-step]')];
    const dimensions = product.querySelector('[data-variant-dimensions]');

    const refreshSteps = () => {
        for (const step of steps) {
            const next = (Number(quantity.value) || 1) + Number(step.dataset.quantityStep);
            step.disabled = quantity.disabled || next < 1 || next > Number(quantity.max);
        }
    };

    for (const step of steps) {
        step.hidden = false;
        step.addEventListener('click', () => {
            quantity.value = (Number(quantity.value) || 1) + Number(step.dataset.quantityStep);
            refreshSteps();
        });
    }
    quantity.addEventListener('input', refreshSteps);
    refreshSteps();

    const refreshVariant = () => {
        const option = variants.selectedOptions[0];
        const units = Number(option.dataset.stock);

        price.textContent = 'Rp' + format.format(Number(option.dataset.price));
        if (!variants.form.hasAttribute('data-preorder')) {
            stock.textContent = option.value ? 'Tersedia (' + format.format(units) + ' unit)' : initialStock;
        }
        quantity.disabled = units < 1;
        addButton.disabled = units < 1;
        quantity.max = Math.max(1, units);
        quantity.value = Math.max(1, Math.min(Number(quantity.value) || 1, Number(quantity.max)));
        for (const button of variantButtons) {
            button.setAttribute('aria-pressed', String(button.dataset.variantChoice === variants.value));
        }
        refreshSteps();
    };

    if (variants) {
        variants.addEventListener('change', refreshVariant);
        if (dimensions) {
            const options = [...variants.options].filter((option) => option.value);
            const buttons = [...dimensions.querySelectorAll('[data-dimension]')];
            const status = dimensions.querySelector('[data-dimension-status]');
            const selected = variants.selectedOptions[0];
            let size = selected.dataset.size ?? '';
            let color = selected.dataset.color ?? '';

            const updateDimensions = () => {
                const match = options.find((option) => !option.disabled && option.dataset.size === size && option.dataset.color === color);
                variants.value = match?.value ?? '';
                for (const button of buttons) {
                    const value = button.dataset.value;
                    const isSize = button.dataset.dimension === 'size';
                    button.disabled = !options.some((option) => !option.disabled && (isSize
                        ? option.dataset.size === value
                        : option.dataset.size === size && option.dataset.color === value));
                    button.setAttribute('aria-pressed', String(value === (isSize ? size : color)));
                }
                status.textContent = match ? '' : size ? 'Pilih warna yang tersedia.' : 'Pilih ukuran terlebih dahulu.';
                refreshVariant();
            };

            variants.hidden = true;
            product.querySelector('[data-dimension-fallback]').hidden = true;
            dimensions.hidden = false;
            for (const button of buttons) {
                button.addEventListener('click', () => {
                    if (button.dataset.dimension === 'size') {
                        size = button.dataset.value;
                        if (!options.some((option) => !option.disabled && option.dataset.size === size && option.dataset.color === color)) {
                            color = '';
                        }
                    } else {
                        color = button.dataset.value;
                    }
                    updateDimensions();
                });
            }
            updateDimensions();
        }
        if (variantButtons.length) {
            variants.hidden = true;
            variantButtons[0].parentElement.hidden = false;
            for (const button of variantButtons) {
                button.addEventListener('click', () => {
                    variants.value = button.dataset.variantChoice;
                    refreshVariant();
                });
            }
        }
        if (variants.selectedIndex > 0 || !addButton.disabled) {
            refreshVariant();
        }
    }
}

for (const carousel of document.querySelectorAll('[data-banner-carousel]')) {
    const slides = [...carousel.querySelectorAll('[data-banner-slide]')];
    const controls = carousel.querySelector('[data-banner-controls]');
    const dots = [...carousel.querySelectorAll('[data-banner-index]')];
    let current = 0;
    let touchStart = null;

    const show = (index) => {
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, position) => {
            const inactive = position !== current;
            slide.toggleAttribute('data-banner-inactive', inactive);
            slide.inert = inactive;
        });
        dots.forEach((dot, position) => dot.setAttribute('aria-current', String(position === current)));
    };

    if (controls) {
        controls.hidden = false;
        carousel.addEventListener('touchstart', (event) => {
            const touch = event.touches[0];
            touchStart = event.touches.length === 1 ? [touch.clientX, touch.clientY] : null;
        }, { passive: true });
        carousel.addEventListener('touchcancel', () => { touchStart = null; });
        carousel.addEventListener('touchend', (event) => {
            if (!touchStart) return;

            const touch = event.changedTouches[0];
            const horizontal = touch.clientX - touchStart[0];
            const vertical = touch.clientY - touchStart[1];
            touchStart = null;

            if (Math.abs(horizontal) > 45 && Math.abs(horizontal) > Math.abs(vertical) * 1.4) {
                show(current + (horizontal < 0 ? 1 : -1));
            }
        }, { passive: true });
        controls.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            if (!button) return;
            show(button.hasAttribute('data-banner-index')
                ? Number(button.dataset.bannerIndex)
                : current + Number(button.dataset.bannerStep));
        });
    }
}

const sort = document.querySelector('[data-store-sort]');
sort?.addEventListener('change', () => sort.form.requestSubmit());
