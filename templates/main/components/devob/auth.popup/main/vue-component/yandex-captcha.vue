<template>
  <div class="devob-yandex-captcha">
    <div :id="captchaId" class="devob-yandex-captcha__container"></div>
  </div>
</template>

<script>
export default {
  name: 'YandexCaptcha',
  emits: ['verified', 'error'],
  props: {
    siteKey: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      captchaId: 'yandex-captcha-' + Math.random().toString(36).substr(2, 9),
      widgetId: null,
      isLoading: false
    };
  },
  mounted() {
    console.log('start captcha');
    this.loadCaptcha();
  },
  beforeUnmount() {
    if (this.widgetId && window.smartCaptcha) {
      try {
        window.smartCaptcha.destroy(this.widgetId);
      } catch (e) {
        console.warn('Ошибка при очистке каптчи:', e);
      }
    }
  },
  methods: {
    loadCaptcha() {
      if (this.isLoading) return;

      // УЛУЧШЕНИЕ: Проверяем валидность ключа
      if (!this.siteKey || this.siteKey.length < 10) {
        console.error('Невалидный siteKey для капчи:', this.siteKey);
        this.$emit('error');
        return;
      }

      if (!window.smartCaptcha) {
        this.isLoading = true; // ИСПРАВЛЕНИЕ: устанавливаем флаг

        const script = document.createElement('script');
        script.src = 'https://smartcaptcha.yandexcloud.net/captcha.js';
        script.onload = () => {
          this.isLoading = false;
          console.log('2222');
          this.renderCaptcha();
        };
        script.onerror = () => {
          this.isLoading = false;
          console.error('Не удалось загрузить скрипт Яндекс SmartCaptcha');
          this.$emit('error');
        };
        document.head.appendChild(script);
      } else {
        this.renderCaptcha();
      }
    },

    renderCaptcha() {
      console.log('🔐 Пробуем рендерить капчу...');
      console.log('siteKey:', this.siteKey);
      console.log('smartCaptcha доступен:', !!window.smartCaptcha);
      if (window.smartCaptcha && this.siteKey) {
        try {
          this.widgetId = window.smartCaptcha.render(this.captchaId, {
            sitekey: this.siteKey,
            callback: this.onSuccess,
            'error-callback': this.onError
          });

          console.log('✅ Капча успешно отрендерена, widgetId:', this.widgetId);
        } catch (e) {
          console.error('Ошибка рендеринга каптчи:', e);
          this.$emit('error');
        }
      }
    },

    onSuccess(token) {
      console.log('✅ Капча пройдена, получен токен');
      this.$emit('verified', token);
    },

    onError() {
      console.error('❌ Ошибка прохождения капчи');
      this.$emit('error');
    },

    reset() {
      if (window.smartCaptcha && this.widgetId) {
        window.smartCaptcha.reset(this.widgetId);
        console.log('🔄 Капча сброшена');
      }
    }
  }
};
</script>
