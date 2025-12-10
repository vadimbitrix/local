document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем Pinia глобально
    if (typeof Pinia !== 'undefined') {
        window.devobPinia = Pinia.createPinia();
    }
});
