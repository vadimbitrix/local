<template>
  <form @submit.prevent="handleSubmit" class="devob-auth-form">
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

    <div class="devob-auth-form__field">
      <label class="devob-auth-form__label">Пароль *</label>
      <div class="devob-auth-form__password-wrapper">
        <input
            :type="showPassword ? 'text' : 'password'"
            v-model="formData.password"
            :class="['devob-auth-form__input', { 'devob-auth-form__input--error': errors.password }]"
            required
        >
        <button
            type="button"
            class="devob-auth-form__password-toggle"
            @click="showPassword = !showPassword"
        >
          <template v-if="showPassword">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </template>
          <template v-else>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </template>
        </button>
      </div>
      <span v-if="errors.password" class="devob-auth-form__error-text">{{ errors.password }}</span>
    </div>

    <div class="devob-auth-form__field">
      <label class="devob-auth-form__label">Имя *</label>
      <input
          type="text"
          v-model="formData.name"
          :class="['devob-auth-form__input', { 'devob-auth-form__input--error': errors.name }]"
          required
      >
      <span v-if="errors.name" class="devob-auth-form__error-text">{{ errors.name }}</span>
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
      {{ loading ? 'Регистрация...' : 'Далее' }}
    </button>

    <div class="devob-auth-form__footer">
      <span>Уже есть аккаунт?</span>
      <a href="#" @click.prevent="$emit('switch-form', 'login')" class="devob-auth-form__link">
        Войти
      </a>
    </div>
  </form>
</template>

<script>
import YandexCaptcha from './yandex-captcha.js';

export default {
  name: 'RegisterForm',
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
        phone: '',
        password: '',
        name: ''
      },
      errors: {},
      loading: false,
      showPassword: false,
      captchaToken: ''
    };
  },
  mounted() {
    console.log('🔍 Register form captchaKey:', this.captchaKey);
    console.log('🔍 Parent data:', this.$parent.captcha_key);

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

      // Получаем чистый номер без маски
      const unmaskedPhone = window.PhoneMask.unmask(this.formData.phone);

      const data = {
        phone: unmaskedPhone,
        password: this.formData.password,
        name: this.formData.name,
        captcha_token: this.captchaToken
      };

      BX.ajax.runComponentAction('devob:auth.popup', 'register', {
        mode: 'class',
        data: data
      }).then((response) => {
        this.loading = false;
        this.$emit('loading', false);

        if (response.data.success) {
          this.$emit('success', response.data);
        } else {
          this.$emit('error', response.data.error);
        }
      }).catch(() => {
        this.loading = false;
        this.$emit('loading', false);
        this.$emit('error', 'Ошибка при регистрации');
      });
    },

    validate() {
      this.errors = {};

      // Валидация телефона с использованием библиотеки
      if (!this.formData.phone.trim()) {
        this.errors.phone = 'Введите номер телефона';
      } else if (!window.PhoneMask.validate(this.formData.phone)) {
        this.errors.phone = 'Неверный формат номера телефона';
      }

      if (!this.formData.password.trim()) {
        this.errors.password = 'Введите пароль';
      } else if (this.formData.password.length < 6) {
        this.errors.password = 'Пароль должен содержать не менее 6 символов';
      }

      if (!this.formData.name.trim()) {
        this.errors.name = 'Введите имя';
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
