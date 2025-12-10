<template>
  <form @submit.prevent="handleSubmit" class="devob-auth-form">
    <div class="devob-sms-info">
      <p class="devob-sms-info__text">
        Мы отправили Вам код подтверждения на телефон
      </p>
      <p class="devob-sms-info__phone">{{ phone }}</p>
    </div>

    <div class="devob-auth-form__field">
      <label class="devob-auth-form__label">Код из SMS *</label>
      <input
          type="text"
          v-model="smsCode"
          :class="['devob-auth-form__input', 'devob-auth-form__input--center', { 'devob-auth-form__input--error': errors.smsCode }]"
          placeholder="Введите код из SMS"
          maxlength="4"
          required
          @input="formatSmsCode"
      >
      <span v-if="errors.smsCode" class="devob-auth-form__error-text">{{ errors.smsCode }}</span>
    </div>

    <button
        type="submit"
        class="devob-auth-form__submit"
        :disabled="loading || !smsCode.trim()"
    >
      {{ loading ? 'Проверка...' : 'Продолжить' }}
    </button>

    <div class="devob-auth-form__resend">
      <button
          type="button"
          class="devob-auth-form__link"
          @click="handleResend"
          :disabled="resendTimer > 0"
      >
        {{ resendTimer > 0 ? `Отправить еще раз через ${resendTimer} сек` : 'Отправить еще раз' }}
      </button>
    </div>

    <div class="devob-auth-form__footer">
      <a href="#" @click.prevent="$emit('back')" class="devob-auth-form__link devob-auth-form__link--cancel">
        Отменить
      </a>
    </div>
  </form>
</template>

<script>
export default {
  name: 'SmsConfirmForm',
  emits: ['loading', 'success', 'error', 'back', 'resend'],
  props: {
    phone: {
      type: String,
      required: true
    },
    componentId: String,
    actionName: {
      type: String,
      default: 'verifySms'
    }
  },
  data() {
    return {
      smsCode: '',
      errors: {},
      loading: false,
      resendTimer: 58,
      timerInterval: null
    };
  },
  mounted() {
    this.startResendTimer();
  },
  beforeUnmount() {
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
  },
  methods: {
    handleSubmit() {
      if (!this.validate()) {
        return;
      }

      this.loading = true;
      this.$emit('loading', true);

      BX.ajax.runComponentAction('devob:auth.popup', this.actionName, {
        mode: 'class',
        data: {
          phone: this.phone,
          code: this.smsCode
        }
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
        this.$emit('error', 'Ошибка при проверке кода');
      });
    },

    handleResend() {
      if (this.resendTimer > 0) return;

      this.$emit('resend');
      this.resendTimer = 58;
      this.startResendTimer();
    },

    startResendTimer() {
      // Очищаем предыдущий таймер
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
      }

      this.timerInterval = setInterval(() => {
        this.resendTimer--;
        if (this.resendTimer <= 0) {
          clearInterval(this.timerInterval);
          this.timerInterval = null;
        }
      }, 1000);
    },

    formatSmsCode() {
      // Оставляем только цифры
      this.smsCode = this.smsCode.replace(/\D/g, '');
    },

    validate() {
      this.errors = {};

      if (!this.smsCode.trim()) {
        this.errors.smsCode = 'Введите код из SMS';
      } else if (this.smsCode.length !== 4) {
        this.errors.smsCode = 'Код должен содержать 4 цифры';
      }

      return Object.keys(this.errors).length === 0;
    }
  }
};
</script>
