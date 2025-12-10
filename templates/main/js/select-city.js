document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.querySelector('[data-select-city-trigger]');
    const popup = document.querySelector('[data-select-city-popup]');
    const backdrop = document.querySelector('.backdrop');
    const body = document.body;

    if (!trigger || !popup || !backdrop) {
        return;
    }

    const closeButton = popup.querySelector('[data-select-city-close]');
    const searchInput = popup.querySelector('[data-select-city-search]');
    const label = document.querySelector('[data-select-city-label]');
    const options = Array.from(popup.querySelectorAll('[data-select-city-option]'));
    const dialog = popup.querySelector('.select-city__dialog');
    const contactsPopup = document.querySelector('.header__contacts-popup');

    let lastFocusedElement = null;
    let backdropWasActivated = false;
    let bodyScrollLocked = false;

    const filterOptions = (value) => {
        const normalizedValue = value.trim().toLowerCase();

        options.forEach((option) => {
            const cityName = option.dataset.city ? option.dataset.city.toLowerCase() : option.textContent.toLowerCase();
            const matches = cityName.includes(normalizedValue);
            const listItem = option.closest('.select-city__item');

            if (!listItem) {
                return;
            }

            listItem.style.display = matches ? '' : 'none';
        });
    };

    const updateSelectedState = (city) => {
        const normalizedCity = city.trim().toLowerCase();

        options.forEach((option) => {
            const isSelected = (option.dataset.city || option.textContent).trim().toLowerCase() === normalizedCity;
            option.classList.toggle('is-selected', isSelected);
        });
    };

    const openPopup = () => {
        if (popup.classList.contains('is-open')) {
            return;
        }

        lastFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;

        popup.classList.add('is-open');
        popup.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');

        backdropWasActivated = !backdrop.classList.contains('is-active-mobile');
        if (backdropWasActivated) {
            backdrop.classList.add('is-active-mobile');
        }

        bodyScrollLocked = !body.classList.contains('no-scroll');
        if (bodyScrollLocked) {
            body.classList.add('no-scroll');
        }

        const currentCity = label ? label.textContent.trim() : '';
        updateSelectedState(currentCity);

        if (searchInput) {
            searchInput.value = '';
            filterOptions('');
            window.requestAnimationFrame(() => {
                searchInput.focus({ preventScroll: true });
            });
        }
    };

    const closePopup = () => {
        if (!popup.classList.contains('is-open')) {
            return;
        }

        popup.classList.remove('is-open');
        popup.setAttribute('aria-hidden', 'true');
        trigger.setAttribute('aria-expanded', 'false');

        if (bodyScrollLocked) {
            body.classList.remove('no-scroll');
        }

        if (backdropWasActivated && (!contactsPopup || !contactsPopup.classList.contains('is-active-mobile'))) {
            backdrop.classList.remove('is-active-mobile');
        }

        backdropWasActivated = false;
        bodyScrollLocked = false;

        if (searchInput) {
            searchInput.value = '';
            filterOptions('');
        }

        if (lastFocusedElement) {
            lastFocusedElement.focus({ preventScroll: true });
        } else {
            trigger.focus({ preventScroll: true });
        }
    };

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        openPopup();
    });

    closeButton?.addEventListener('click', closePopup);

    backdrop.addEventListener('click', () => {
        if (popup.classList.contains('is-open')) {
            closePopup();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && popup.classList.contains('is-open')) {
            closePopup();
        }
    });

    searchInput?.addEventListener('input', (event) => {
        const value = event.target.value;
        filterOptions(value);
    });

    popup.addEventListener('click', (event) => {
        if (dialog && !event.target.closest('.select-city__dialog')) {
            closePopup();
            return;
        }

        const option = event.target.closest('[data-select-city-option]');

        if (!option) {
            return;
        }

        const city = option.dataset.city || option.textContent.trim();

        if (label) {
            label.textContent = city;
        }

        updateSelectedState(city);
        closePopup();
    });
});
