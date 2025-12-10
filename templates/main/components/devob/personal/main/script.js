document.addEventListener('DOMContentLoaded', () => {
    const archiveLists = Array.from(document.querySelectorAll('[data-personal-orders-archive]'));

    archiveLists.forEach((list) => {
        const items = Array.from(list.querySelectorAll('[data-personal-order-item]'));

        if (!items.length) {
            return;
        }

        const collapseItem = (item) => {
            const content = item.querySelector('.personal-orders__item-content');
            const trigger = item.querySelector('.personal-orders__accordion-trigger');

            if (!content || !trigger) {
                return;
            }

            if (content.style.maxHeight === 'none' || content.style.maxHeight === '') {
                content.style.maxHeight = `${content.scrollHeight}px`;
            }

            item.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');

            void content.offsetHeight;

            requestAnimationFrame(() => {
                content.style.maxHeight = '0px';
            });
        };

        const expandItem = (item) => {
            const content = item.querySelector('.personal-orders__item-content');
            const trigger = item.querySelector('.personal-orders__accordion-trigger');

            if (!content || !trigger) {
                return;
            }

            content.style.maxHeight = `${content.scrollHeight}px`;
            item.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        };

        items.forEach((item) => {
            const trigger = item.querySelector('.personal-orders__accordion-trigger');
            const content = item.querySelector('.personal-orders__item-content');

            if (!trigger || !content) {
                return;
            }

            const isInitiallyOpen = item.classList.contains('is-open');

            if (isInitiallyOpen) {
                content.style.maxHeight = 'none';
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                content.style.maxHeight = '0px';
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', () => {
                if (item.classList.contains('is-open')) {
                    collapseItem(item);
                    return;
                }

                items.forEach((currentItem) => {
                    if (currentItem !== item && currentItem.classList.contains('is-open')) {
                        collapseItem(currentItem);
                    }
                });

                expandItem(item);
            });

            content.addEventListener('transitionend', (event) => {
                if (event.propertyName !== 'max-height') {
                    return;
                }

                if (item.classList.contains('is-open')) {
                    content.style.maxHeight = 'none';
                }
            });
        });
    });
});
