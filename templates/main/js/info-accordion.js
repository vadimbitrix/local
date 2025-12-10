document.addEventListener('DOMContentLoaded', () => {
    const accordion = document.querySelector('.info-accordion');

    if (!accordion) {
        return;
    }

    const items = Array.from(accordion.querySelectorAll('.info-accordion__item'));

    const collapseItem = (item) => {
        const content = item.querySelector('.info-accordion__content');

        if (!content) {
            return;
        }

        const currentHeight = content.scrollHeight;

        if (content.style.maxHeight === 'none' || content.style.maxHeight === '') {
            content.style.maxHeight = `${currentHeight}px`;
        }

        void content.offsetHeight;

        requestAnimationFrame(() => {
            content.style.maxHeight = '0px';
        });

        item.classList.remove('is-open');
        const trigger = item.querySelector('.info-accordion__trigger');

        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
    };

    const expandItem = (item) => {
        const content = item.querySelector('.info-accordion__content');

        if (!content) {
            return;
        }

        content.style.maxHeight = `${content.scrollHeight}px`;
        item.classList.add('is-open');

        const trigger = item.querySelector('.info-accordion__trigger');

        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
        }
    };

    const openItem = (target) => {
        items.forEach((item) => {
            if (item === target) {
                expandItem(item);
            } else {
                collapseItem(item);
            }
        });
    };

    items.forEach((item, index) => {
        const trigger = item.querySelector('.info-accordion__trigger');
        const content = item.querySelector('.info-accordion__content');

        if (!trigger || !content) {
            return;
        }

        trigger.setAttribute('aria-expanded', index === 0 ? 'true' : 'false');
        trigger.setAttribute('aria-controls', `info-accordion-panel-${index}`);
        trigger.setAttribute('id', `info-accordion-trigger-${index}`);

        content.setAttribute('id', `info-accordion-panel-${index}`);
        content.setAttribute('role', 'region');
        content.setAttribute('aria-labelledby', `info-accordion-trigger-${index}`);

        if (index === 0) {
            expandItem(item);
            content.style.maxHeight = 'none';
        } else {
            content.style.maxHeight = '0px';
            item.classList.remove('is-open');
        }

        trigger.addEventListener('click', () => {
            if (item.classList.contains('is-open')) {
                return;
            }

            openItem(item);
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
