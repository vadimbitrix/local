class CookieNotification {
    constructor({
            cookieName        = 'cookies_accepted',
            expireHours       = 24,    // срок хранения
            showDelayMs       = 1000   // задержка показа
        } = {}) {

        this.cookieName   = cookieName;
        this.expireHours  = expireHours;
        this.showDelayMs  = showDelayMs;

        this.notification = document.getElementById('cookieNotification');
        this.closeButton  = document.getElementById('cookieClose');
        this.acceptButton = document.getElementById('cookieAccept');
        this.learnMoreBtn = document.getElementById('cookieLearnMore');

        this.init();
    }

    init() {
        if (!this.getCookie(this.cookieName)) {
            setTimeout(() => this.showNotification(), this.showDelayMs);
        }
        this.bindEvents();
    }

    bindEvents() {
        this.closeButton ?.addEventListener('click', () => this.hideNotification());
        this.acceptButton?.addEventListener('click', () => this.acceptCookies());
        this.learnMoreBtn?.addEventListener('click', () => this.showMoreInfo());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isVisible()) this.hideNotification();
        });
    }

    showNotification() {
        if (!this.notification) return;
        this.notification.classList.remove('cookie-notification--hidden');
        requestAnimationFrame(() => { this.notification.style.opacity = '1'; });
    }

    hideNotification() {
        if (!this.notification) return;
        this.notification.classList.add('cookie-notification--hiding');
        setTimeout(() => {
            this.notification.classList.add   ('cookie-notification--hidden');
            this.notification.classList.remove('cookie-notification--hiding');
        }, 300);
    }

    acceptCookies() {
        this.setCookie(this.cookieName, 'true', this.expireHours);
        if (typeof window.onCookiesAccepted === 'function') window.onCookiesAccepted();
        this.hideNotification();
        this.enableAnalytics();
    }

    showMoreInfo() {
        window.open('/privacy-policy/', '_blank');
    }

    enableAnalytics() {
        if (typeof gtag === 'function') {
            gtag('consent', 'update', { analytics_storage: 'granted' });
        }
    }

    setCookie(name, value, hours) {
        const expires = new Date(Date.now() + hours * 3600_000).toUTCString();
        document.cookie = `${name}=${value};expires=${expires};path=/`;
    }

    getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    isVisible() {
        return this.notification && !this.notification.classList.contains('cookie-notification--hidden');
    }

    forceShow() {
        this.setCookie(this.cookieName, '', -1);
        this.showNotification();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.cookieNotification = new CookieNotification();
});
