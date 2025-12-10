<template>
  <form @submit.prevent="handleSubmit" class="devob-auth-form">
    <div class="devob-recovery-info devob-recovery-info--success">
      <p class="devob-recovery-info__text">
        Придумайте новый пароль для аккаунта с номером <strong>{{ formattedPhone }}</strong>.
      </p>
    </div>

    <div class="devob-auth-form__field">
      <label class="devob-auth-form__label">Новый пароль *</label>
      <div class="devob-auth-form__password-wrapper">
        <input
            :type="showPassword ? 'text' : 'password'"
            v-model="formData.password"
            :class="['devob-auth-form__input', { 'devob-auth-form__input--error': errors.password }]"
            autocomplete="new-password"
            required
        >
        <button
            type="button"
            class="devob-auth-form__password-toggle"
            @click="showPassword = !showPassword"
        >
          <template v-if="showPassword">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </template>
          <template v-else>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </template>
        </button>
      </div>
      <span v-if="errors.password" class="devob-auth-form__error-text">{{ errors.password }}</span>
    </div>

    <div class="devob-auth-form__field">
      <label class="devob-auth-form__label">Повторите пароль *</label>
      <input
          :type="showPassword ? 'text' : 'password'"
          v-model="formData.confirmPassword"
          :class="['devob-auth-form__input', { 'devob-auth-form__input--error': errors.confirmPassword }]"
          autocomplete="new-password"
          required
      >
      <span v-if="errors.confirmPassword" class="devob-auth-form__error-text">{{ errors.confirmPassword }}</span>
    </div>

    <button
        type="submit"
        class="devob-auth-form__submit"
        :disabled="loading"
    >
      {{ loading ? 'Сохраняем...' : 'Сохранить пароль' }}
    </button>
  </form>
</template>

<script>
export default {
  name: 'PasswordResetForm',
  emits: ['loading', 'success', 'error'],
  props: {
    phone: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      formData: {
        password: '',
        confirmPassword: ''
      },
      errors: {},
      loading: false,
      showPassword: false
    };
  },
  computed: {
    formattedPhone() {
      const digits = (this.phone || '').replace(/\D/g, '');
      if (digits.length === 11) {
        return `+${digits[0]} (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9, 11)}`;
      }
      if (digits.length === 10) {
        return `+7 (${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 8)}-${digits.slice(8, 10)}`;
      }
      return `+${digits}`;
    }
  },
  methods: {
    handleSubmit() {
      if (!this.validate()) {
        return;
      }

      this.loading = true;
      this.$emit('loading', true);

      BX.ajax.runComponentAction('devob:auth.popup', 'resetPassword', {
        mode: 'class',
        data: {
          phone: this.phone,
          password: this.formData.password,
          confirm_password: this.formData.confirmPassword
        }
      }).then((response) => {
        this.loading = false;
        this.$emit('loading', false);

        if (response.data.success) {
          this.$emit('success', response.data);
        } else {
          this.$emit('error', response.data.error || 'Не удалось сохранить пароль');
        }
      }).catch(() => {
        this.loading = false;
        this.$emit('loading', false);
        this.$emit('error', 'Ошибка при сохранении пароля');
      });
    },

    validate() {
      this.errors = {};

      if (!this.formData.password.trim()) {
        this.errors.password = 'Введите новый пароль';
      } else if (this.formData.password.length < 6) {
        this.errors.password = 'Пароль должен содержать не менее 6 символов';
      }

      if (!this.formData.confirmPassword.trim()) {
        this.errors.confirmPassword = 'Повторите пароль';
      } else if (this.formData.password !== this.formData.confirmPassword) {
        this.errors.confirmPassword = 'Пароли не совпадают';
      }

      return Object.keys(this.errors).length === 0;
    }
  }
};
</script>
