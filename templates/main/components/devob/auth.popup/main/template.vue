<template>
  <div class="devob-auth-popup" v-if="showPopup">
    <div class="devob-auth-popup__overlay" @click="closePopup"></div>
    <div class="devob-auth-popup__content">
      <div class="devob-auth-popup__header">
        <h2 class="devob-auth-popup__title">{{ currentTitle }}</h2>
        <button class="devob-auth-popup__close" @click="closePopup">×</button>
      </div>

      <div class="devob-auth-popup__body">
        <!-- Форма входа -->
        <login-form
            v-if="currentForm === 'login'"
            :captcha-key="actualCaptchaKey"
            :component-id="componentId"
            @success="handleSuccess"
            @error="handleError"
            @switch-form="switchForm"
            @loading="setLoading"
        />

        <!-- Форма регистрации -->
        <register-form
            v-if="currentForm === 'register'"
            :captcha-key="actualCaptchaKey"
            :component-id="componentId"
            @success="handleRegisterSuccess"
            @error="handleError"
            @switch-form="switchForm"
            @loading="setLoading"
        />

        <!-- Форма подтверждения SMS при регистрации -->
        <sms-confirm-form
            v-if="currentForm === 'sms-confirm'"
            :phone="confirmationPhone"
            :component-id="componentId"
            @success="handleSuccess"
            @error="handleError"
            @back="switchForm('register')"
            @resend="handleRegisterResendSms"
            @loading="setLoading"
        />

        <!-- Шаг 1: ввод телефона для восстановления -->
        <password-recovery-form
            v-if="currentForm === 'recovery-phone'"
            :captcha-key="actualCaptchaKey"
            :component-id="componentId"
            @success="handleRecoveryPhoneSuccess"
            @error="handleError"
            @switch-form="switchForm"
            @loading="setLoading"
        />

        <!-- Шаг 2: подтверждение SMS для восстановления -->
        <sms-confirm-form
            v-if="currentForm === 'recovery-sms'"
            :phone="recoveryPhone"
            :component-id="componentId"
            action-name="verifyRecoveryCode"
            @success="handleRecoverySmsSuccess"
            @error="handleError"
            @back="switchForm('recovery-phone')"
            @resend="handleRecoveryResendSms"
            @loading="setLoading"
        />

        <!-- Шаг 3: установка нового пароля -->
        <password-reset-form
            v-if="currentForm === 'recovery-password'"
            :phone="recoveryPhone"
            @success="handleRecoveryPasswordSuccess"
            @error="handleError"
            @loading="setLoading"
        />
      </div>

      <!-- Индикатор загрузки -->
      <div v-if="loading" class="devob-auth-popup__loader">
        <div class="devob-auth-popup__spinner"></div>
      </div>

      <!-- Сообщения об ошибках -->
      <div v-if="errorMessage" class="devob-auth-popup__error">
        {{ errorMessage }}
      </div>

      <!-- Сообщения об успехе -->
      <div v-if="successMessage" class="devob-auth-popup__success">
        {{ successMessage }}
      </div>
    </div>
  </div>
</template>
<script>
import LoginForm from './vue-component/login-form.js';
import RegisterForm from './vue-component/register-form.js';
import SmsConfirmForm from './vue-component/sms-confirm-form.js';
import PasswordRecoveryForm from './vue-component/password-recovery-form.js';
import PasswordResetForm from './vue-component/password-reset-form.js';

export default {
  name: 'DevobAuthPopup',
  components: {
    LoginForm,
    RegisterForm,
    SmsConfirmForm,
    PasswordRecoveryForm,
    PasswordResetForm
  },
  props: {
    captchaKey: {
      type: String,
      default: ''
    },
    componentId: {
      type: String,
      required: true
    },
    isAuthorized: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      showPopup: false,
      currentForm: 'login', // login, register, sms-confirm, recovery-phone, recovery-sms, recovery-password
      loading: false,
      errorMessage: '',
      successMessage: '',
      confirmationPhone: '',
      recoveryPhone: ''
    };
  },
  computed: {
    currentTitle() {
      const titles = {
        login: 'Вход в личный кабинет',
        register: 'Регистрация по телефону',
        'sms-confirm': 'Подтверждение номера телефона',
        'recovery-phone': 'Восстановление пароля',
        'recovery-sms': 'Подтвердите номер телефона',
        'recovery-password': 'Придумайте новый пароль'
      };
      return titles[this.currentForm] || titles['recovery-phone'];
    },
    actualCaptchaKey() {
      return this.captcha_key || this.captchaKey || ''; // captcha_key из PHP данных
    }
  },
  mounted() {
    // Глобальные обработчики для открытия popup
    window.devobAuthPopup = {
      open: this.openPopup,
      close: this.closePopup,
      switchTo: this.switchForm
    };

    // Обработчик клавиши ESC
    document.addEventListener('keydown', this.handleKeydown);

    // Автоматическое открытие если есть параметр в URL
    if (window.location.hash === '#auth' || window.location.search.includes('auth=popup')) {
      this.openPopup();
    }
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.handleKeydown);

    // Очищаем глобальную переменную
    if (window.devobAuthPopup) {
      delete window.devobAuthPopup;
    }

    // Восстанавливаем overflow body
    document.body.style.overflow = '';
  },
  methods: {
    openPopup(form = 'login') {
      const targetForm = form === 'recovery' ? 'recovery-phone' : form;
      this.showPopup = true;
      this.currentForm = targetForm;
      if (targetForm === 'login') {
        this.resetRecoveryState();
        this.confirmationPhone = '';
      }
      this.clearMessages();
      document.body.style.overflow = 'hidden';
    },

    closePopup() {
      this.showPopup = false;
      this.clearMessages();
      this.resetRecoveryState();
      this.confirmationPhone = '';
      document.body.style.overflow = '';

      // Очищаем URL от auth параметров
      if (window.location.hash === '#auth') {
        window.location.hash = '';
      }
    },

    switchForm(formName) {
      const targetForm = formName === 'recovery' ? 'recovery-phone' : formName;
      if (targetForm === 'login') {
        this.resetRecoveryState();
        this.confirmationPhone = '';
      }
      this.currentForm = targetForm;
      this.clearMessages();
    },

    setLoading(status) {
      this.loading = status;
    },

    handleSuccess(data) {
      this.loading = false;

      if (data.redirect) {
        this.successMessage = data.message || 'Авторизация успешна! Перенаправляем...';
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 1500);
      } else {
        this.successMessage = data.message || 'Операция выполнена успешно!';
        setTimeout(() => {
          this.closePopup();
        }, 2000);
      }
    },

    handleRegisterSuccess(data) {
      this.loading = false;

      if (data.need_sms_confirmation) {
        this.confirmationPhone = data.phone;
        this.switchForm('sms-confirm');
        this.successMessage = 'Код подтверждения отправлен на ваш номер';
      } else {
        this.handleSuccess(data);
      }
    },

    handleRecoveryPhoneSuccess(data) {
      this.loading = false;
      this.recoveryPhone = data.phone;
      this.switchForm('recovery-sms');
      this.successMessage = data.message || 'Код подтверждения отправлен на ваш номер';
    },

    handleRecoverySmsSuccess(data) {
      this.loading = false;
      this.switchForm('recovery-password');
      this.successMessage = data.message || 'Код подтверждён. Придумайте новый пароль.';
    },

    handleRecoveryPasswordSuccess(data) {
      this.loading = false;
      this.handleSuccess(data);
    },

    handleError(error) {
      this.loading = false;
      this.errorMessage = typeof error === 'string' ? error : ((error && error.message) || 'Произошла ошибка');
    },

    handleRegisterResendSms() {
      if (!this.confirmationPhone) {
        return;
      }

      this.setLoading(true);

      BX.ajax.runComponentAction('devob:auth.popup', 'sendSms', {
        mode: 'class',
        data: {
          phone: this.confirmationPhone
        }
      }).then((response) => {
        this.loading = false;
        if (response.data.success) {
          this.successMessage = 'Код отправлен повторно';
        } else {
          this.errorMessage = response.data.error || 'Ошибка при отправке SMS';
        }
      }).catch(() => {
        this.loading = false;
        this.errorMessage = 'Ошибка при отправке SMS';
      });
    },

    handleRecoveryResendSms() {
      if (!this.recoveryPhone) {
        return;
      }

      this.setLoading(true);

      BX.ajax.runComponentAction('devob:auth.popup', 'sendRecoverySms', {
        mode: 'class',
        data: {
          phone: this.recoveryPhone
        }
      }).then((response) => {
        this.loading = false;
        if (response.data.success) {
          this.successMessage = response.data.message || 'Код отправлен повторно';
        } else {
          this.errorMessage = response.data.error || 'Ошибка при отправке SMS';
        }
      }).catch(() => {
        this.loading = false;
        this.errorMessage = 'Ошибка при отправке SMS';
      });
    },

    resetRecoveryState() {
      this.recoveryPhone = '';
    },

    clearMessages() {
      this.errorMessage = '';
      this.successMessage = '';
    },

    handleKeydown(event) {
      if (event.key === 'Escape' && this.showPopup) {
        this.closePopup();
      }
    }
  }
};
</script>
