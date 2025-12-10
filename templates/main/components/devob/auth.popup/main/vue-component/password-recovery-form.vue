<template>
  <form @submit.prevent="handleSubmit" class="devob-auth-form">
    <div class="devob-recovery-info">
      <p class="devob-recovery-info__text">
        Укажите номер телефона, который использовали при регистрации. Мы отправим SMS с кодом для восстановления доступа.
      </p>
    </div>

    <div class="devob-auth-form__field">
      <label class="devob-auth-form__label">Номер телефона *</label>
      <input
          ref="phoneInput"
          type="tel"
          v-model="formData.phone"
          :class="['devob-auth-form__input', { 'devob-auth-form__input--error': errors.phone }]"
          required
      >
      <span v-if="errors.phone" class="devob-auth-form__error-text">{{ errors.phone }}</span>
    </div>

    <yandex-captcha
        v-if="captchaKey"
        :site-key="captchaKey"
        @verified="handleCaptchaVerified"
        @error="handleCaptchaError"
    />

    <button
        type="submit"
        class="devob-auth-form__submit"
        :disabled="loading || (captchaKey && captchaKey.trim() && !captchaToken)"
    >
      {{ loading ? 'Отправляем...' : 'Получить код' }}
    </button>

    <div class="devob-auth-form__footer">
      <a href="#" @click.prevent="$emit('switch-form', 'login')" class="devob-auth-form__link">
        Вернуться к входу
      </a>
    </div>
  </form>
</template>

<script>
import YandexCaptcha from './yandex-captcha.js';

export default {
  name: 'PasswordRecoveryForm',
  emits: ['loading', 'success', 'error', 'switch-form'],
  components: {
    YandexCaptcha
  },
  props: {
    captchaKey: String,
    componentId: String
  },
  data() {
    return {
      formData: {
        phone: ''
      },
      errors: {},
      loading: false,
      captchaToken: ''
    };
  },
  mounted() {
    // Применяем маску к input телефона
    this.$nextTick(() => {
      window.PhoneMask.applyMask(this.$refs.phoneInput);
    });
  },
  beforeUnmount() {
    // Удаляем маску при удалении компонента
    window.PhoneMask.removeMask(this.$refs.phoneInput);
  },
  methods: {
    handleSubmit() {
      if (!this.validate()) {
        return;
      }

      this.loading = true;
      this.$emit('loading', true);

      const unmaskedPhone = window.PhoneMask.unmask(this.formData.phone);

      BX.ajax.runComponentAction('devob:auth.popup', 'sendRecoverySms', {
        mode: 'class',
        data: {
          phone: unmaskedPhone,
          captcha_token: this.captchaToken
        }
      }).then((response) => {
        this.loading = false;
        this.$emit('loading', false);

        if (response.data.success) {
          this.$emit('success', response.data);
        } else {
          this.$emit('error', response.data.error || 'Не удалось отправить SMS');
        }
      }).catch(() => {
        this.loading = false;
        this.$emit('loading', false);
        this.$emit('error', 'Ошибка при отправке запроса');
      });
    },

    validate() {
      this.errors = {};

      if (!this.formData.phone.trim()) {
        this.errors.phone = 'Введите номер телефона';
      } else if (!window.PhoneMask.validate(this.formData.phone)) {
        this.errors.phone = 'Неверный формат номера телефона';
      }

      return Object.keys(this.errors).length === 0;
    },

    handleCaptchaVerified(token) {
      this.captchaToken = token;
    },

    handleCaptchaError() {
      this.captchaToken = '';
      this.$emit('error', 'Ошибка каптчи');
    }
  }
};
</script>
