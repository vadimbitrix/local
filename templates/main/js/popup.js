document.addEventListener('DOMContentLoaded', () => {
    const contactsTrigger = document.querySelector('.header__contacts-trigger');
    const contactsPopup = document.querySelector('.header__contacts-popup');
    const backdrop = document.querySelector('.backdrop');
    const body = document.body;
    const ordersLoginTrigger = document.querySelector('.js-header-orders-login');

    if (ordersLoginTrigger) {
        ordersLoginTrigger.addEventListener('click', (event) => {
            event.preventDefault();
            window.devobAuthPopup?.open('login');
        });
    }

    // Проверка, что все элементы найдены
    if (!contactsTrigger || !contactsPopup || !backdrop) {
        return;
    }

    const openMobileMenu = () => {
        contactsPopup.classList.add('is-active-mobile');
        backdrop.classList.add('is-active-mobile');
        body.classList.add('no-scroll');
        contactsTrigger.setAttribute('aria-expanded', 'true');
        contactsPopup.setAttribute('aria-hidden', 'false');
    };

    const closeMobileMenu = () => {
        contactsPopup.classList.remove('is-active-mobile');
        backdrop.classList.remove('is-active-mobile');
        body.classList.remove('no-scroll');
        contactsTrigger.setAttribute('aria-expanded', 'false');
        contactsPopup.setAttribute('aria-hidden', 'true');
    };

    contactsTrigger.addEventListener('click', () => {
        // Логика работает только на экранах, где CSS hover нежелателен (мобильные)
        if (window.innerWidth <= 768) {
            const isMenuOpen = contactsPopup.classList.contains('is-active-mobile');
            if (isMenuOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        }
    });

    backdrop.addEventListener('click', closeMobileMenu);
});
