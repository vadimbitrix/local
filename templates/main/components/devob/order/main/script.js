function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define(["exports"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports);
    global.orderTpl = mod.exports;
  }
})(typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : this, function (_exports) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
  function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
  function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
  function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
  function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
  function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
  function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
  function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
  function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
  var _default = _exports.default = {
    template: "<div class=\"checkout-page\"> <div v-if=\"isOrderCompleted\" class=\"checkout-success\"> <div class=\"checkout-success__icon\" aria-hidden=\"true\">✓</div> <h1 class=\"checkout-success__title\">{{ state.submitSuccess || 'Заказ успешно оформлен' }}</h1> <p class=\"checkout-success__subtitle\"> Мы отправили подтверждение заказа. Менеджер свяжется с вами при необходимости. </p> <dl class=\"checkout-success__details\"> <div class=\"checkout-success__details-item\"> <dt>Номер заказа</dt> <dd>{{ orderNumber }}</dd> </div> <div v-if=\"orderDateFormatted\" class=\"checkout-success__details-item\"> <dt>Дата оформления</dt> <dd>{{ orderDateFormatted }}</dd> </div> </dl> <div class=\"checkout-success__actions\"> <a class=\"checkout-button checkout-button--primary\" href=\"/personal/orders/\">Перейти в мои заказы</a> <a class=\"checkout-button checkout-button--outline\" href=\"/catalog/\">Вернуться в каталог</a> </div> </div> <div class=\"checkout-page__layout\" v-else-if=\"hasItems\"> <main class=\"checkout-page__main\"> <section class=\"checkout-section\"> <h1 class=\"checkout-section__title\">1. Где и как вы хотите получить заказ</h1> <div class=\"checkout-block\"> <label class=\"checkout-label\" for=\"checkout-address\">Введите город и выберите способ доставки</label> <div class=\"checkout-address\"> <input id=\"checkout-address\" type=\"text\" class=\"checkout-input\" autocomplete=\"off\" v-model=\"form.addressQuery\" :placeholder=\"addressPlaceholder\" @focus=\"onAddressInput\" @input=\"onAddressInput\" /> <button v-if=\"form.selectedAddress && !state.isLoadingSuggestions\" type=\"button\" class=\"checkout-input__clear\" @click=\"clearAddress\" aria-label=\"Очистить адрес\" > × </button> <span v-else-if=\"state.isLoadingSuggestions\" class=\"checkout-input__spinner\" role=\"status\" aria-label=\"Ищем адреса\" > <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" aria-hidden=\"true\" > <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99\" /> </svg> </span> <ul v-if=\"addressSuggestions.length\" class=\"checkout-suggestions\"> <li v-for=\"item in addressSuggestions\" :key=\"item.value + item.postalCode\" class=\"checkout-suggestions__item\" @mousedown.prevent=\"selectAddress(item)\" > <div class=\"checkout-suggestions__title\">{{ item.value }}</div> <div class=\"checkout-suggestions__meta\"> <span>{{ item.region }}</span> <span v-if=\"item.hasTerminals\" class=\"checkout-tag checkout-tag--success\">Есть терминалы СДЭК</span> <span v-else class=\"checkout-tag checkout-tag--warning\">Нет терминалов СДЭК</span> </div> </li> </ul> </div> </div> <div class=\"checkout-block checkout-block--delivery\"> <h2 class=\"checkout-subtitle\">Выберите способ доставки</h2> <div class=\"checkout-delivery\"> <button v-for=\"method in deliveryMethods\" :key=\"method.code\" type=\"button\" class=\"checkout-delivery__item\" :class=\"{ 'checkout-delivery__item--active': form.deliveryType === method.code, 'checkout-delivery__item--disabled': isDeliveryMethodDisabled(method) }\" :disabled=\"isDeliveryMethodDisabled(method)\" @click=\"selectDelivery(method.code)\" > <span class=\"checkout-delivery__icon\" aria-hidden=\"true\"> <svg v-if=\"method.code === 'terminal'\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" > <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"m7.875 14.25 1.214 1.942a2.25 2.25 0 0 0 1.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 0 1 1.872 1.002l.164.246a2.25 2.25 0 0 0 1.872 1.002h2.092a2.25 2.25 0 0 0 1.872-1.002l.164-.246A2.25 2.25 0 0 1 16.954 9h4.636M2.41 9a2.25 2.25 0 0 0-.16.832V12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 12V9.832c0-.287-.055-.57-.16-.832M2.41 9a2.25 2.25 0 0 1 .382-.632l3.285-3.832a2.25 2.25 0 0 1 1.708-.786h8.43c.657 0 1.281.287 1.709.786l3.284 3.832c.163.19.291.404.382.632M4.5 20.25h15A2.25 2.25 0 0 0 21.75 18v-2.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25Z\" /> </svg> <svg v-else-if=\"method.code === 'courier'\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" > <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12\" /> </svg> <svg v-else-if=\"method.code === 'pickup'\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" > <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z\" /> </svg> <svg v-else xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" > <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M3.75 4.5h16.5M3.75 9h16.5M3.75 13.5h16.5M3.75 18h16.5\" /> </svg> </span> <span class=\"checkout-delivery__content\"> <span class=\"checkout-delivery__title\">{{ method.title }}</span> <span class=\"checkout-delivery__description\">{{ method.description }}</span> <span class=\"checkout-delivery__price\"> <template v-if=\"method.loading\">Рассчитываем...</template> <template v-else-if=\"method.price\">{{ method.price }}</template> <template v-else>—</template> </span> </span> </button> </div> <transition name=\"checkout-fade\"> <div v-if=\"form.deliveryType === 'terminal'\"> <p v-if=\"state.isLoadingDeliveryCost\" class=\"checkout-hint checkout-hint--muted\"> Рассчитываем стоимость доставки... </p> <p v-if=\"state.deliveryCostError\" class=\"checkout-error\">{{ state.deliveryCostError }}</p> <div v-if=\"form.terminal.code\" class=\"checkout-terminal\"> <div class=\"checkout-terminal__header\"> <h3 class=\"checkout-terminal__title\">{{ form.terminal.name }}</h3> <button type=\"button\" class=\"checkout-terminal__change\" @click=\"openTerminalModal\">Выбрать другой пункт</button> </div> <p class=\"checkout-terminal__line\">{{ form.terminal.address }}</p> <p v-if=\"form.terminal.workTime\" class=\"checkout-terminal__line\">График работы: {{ form.terminal.workTime }}</p> <p v-if=\"form.terminal.phones && form.terminal.phones.length\" class=\"checkout-terminal__line\"> Телефон: {{ form.terminal.phones.join(', ') }} </p> <p v-if=\"form.terminal.note\" class=\"checkout-terminal__note\">{{ form.terminal.note }}</p> </div> <div v-if=\"state.terminalError\" class=\"checkout-error\">{{ state.terminalError }}</div> </div> </transition> <transition name=\"checkout-fade\"> <div v-if=\"form.deliveryType === 'courier'\" class=\"checkout-delivery-details\"> <div class=\"checkout-grid\"> <label class=\"checkout-field\"> <span class=\"checkout-field__label\">Город</span> <input type=\"text\" class=\"checkout-input\" v-model=\"form.courier.city\" placeholder=\"Например: Казань\" /> </label> <label class=\"checkout-field\"> <span class=\"checkout-field__label\">Улица</span> <input type=\"text\" class=\"checkout-input\" v-model=\"form.courier.street\" placeholder=\"Улица\" /> </label> </div> <div class=\"checkout-grid checkout-grid--small\"> <label class=\"checkout-field\"> <span class=\"checkout-field__label\">Дом / Корпус</span> <input type=\"text\" class=\"checkout-input\" v-model=\"form.courier.house\" placeholder=\"Дом\" /> </label> <label class=\"checkout-field\"> <span class=\"checkout-field__label\">Квартира</span> <input type=\"text\" class=\"checkout-input\" v-model=\"form.courier.flat\" placeholder=\"Квартира\" /> </label> </div> <p v-if=\"state.deliveryCostError\" class=\"checkout-error\">{{ state.deliveryCostError }}</p> <label class=\"checkout-field\"> <span class=\"checkout-field__label\">Комментарий для курьера</span> <textarea class=\"checkout-textarea\" rows=\"2\" v-model=\"form.courier.comment\" placeholder=\"Подробности для курьера\" ></textarea> </label> </div> </transition> <transition name=\"checkout-fade\"> <div v-if=\"form.deliveryType === 'pickup'\" class=\"checkout-delivery-details\"> <div class=\"checkout-alert checkout-alert--warning\"> <strong>Важно!</strong> При выборе самовывоза товары нужно будет самостоятельно забрать из филиала. </div> <div class=\"checkout-pickup-list\"> <article v-for=\"item in pickupHints\" :key=\"item.productId\" class=\"checkout-pickup-item\"> <header class=\"checkout-pickup-item__header\"> <h3 class=\"checkout-pickup-item__title\">{{ item.name }}</h3> </header> <p class=\"checkout-pickup-item__line\">Адрес: {{ item.address }}</p> <p class=\"checkout-pickup-item__line\">График работы: {{ item.schedule }}</p> </article> </div> </div> </transition> </div> </section> <section class=\"checkout-section\"> <h2 class=\"checkout-section__title\">2. Как вам будет удобнее оплатить заказ?</h2> <div class=\"checkout-payment\"> <label v-for=\"method in paymentMethods\" :key=\"method.code\" class=\"checkout-payment__item\" :class=\"{ 'checkout-payment__item--active': form.paymentType === method.code }\" > <input class=\"checkout-payment__radio\" type=\"radio\" name=\"checkout-payment\" :value=\"method.code\" v-model=\"form.paymentType\" /> <span class=\"checkout-payment__title\">{{ method.title }}</span> <span class=\"checkout-payment__description\">{{ method.description }}</span> </label> </div> </section> <section class=\"checkout-section\"> <h2 class=\"checkout-section__title\">3. Укажите данные получателя заказа</h2> <div class=\"checkout-grid\"> <label :class=\"['checkout-field', { 'checkout-field--error': isFieldInvalid('phone') }]\"> <span class=\"checkout-field__label\"> Номер телефона <span class=\"checkout-field__required\" aria-hidden=\"true\">*</span> </span> <input type=\"tel\" :class=\"['checkout-input', { 'checkout-input--error': isFieldInvalid('phone') }]\" v-model=\"form.recipient.phone\" placeholder=\"+7 (___) ___-__-__\" :disabled=\"isPhoneLocked\" /> <span v-if=\"isFieldInvalid('phone')\" class=\"checkout-field__error\">{{ getFieldError('phone') }}</span> </label> <div v-if=\"requiresSmsVerification\" class=\"checkout-sms\"> <template v-if=\"smsVerification.isVerified\"> <p class=\"checkout-hint checkout-hint--success\"> {{ smsVerification.status || 'Телефон подтверждён. Вы можете завершить оформление заказа.' }} </p> </template> <template v-else> <p class=\"checkout-hint checkout-hint--muted\"> После заполнения обязательных полей нажмите «Оформить заказ», мы отправим SMS с кодом подтверждения. </p> <p v-if=\"smsVerification.status\" class=\"checkout-hint checkout-hint--success\"> {{ smsVerification.status }} </p> <p v-if=\"smsVerification.error\" class=\"checkout-error\">{{ smsVerification.error }}</p> </template> </div> <label :class=\"['checkout-field', { 'checkout-field--error': isFieldInvalid('email') }]\"> <span class=\"checkout-field__label\"> E-mail <span class=\"checkout-field__required\" aria-hidden=\"true\">*</span> </span> <input type=\"email\" :class=\"['checkout-input', { 'checkout-input--error': isFieldInvalid('email') }]\" v-model=\"form.recipient.email\" placeholder=\"email@example.com\" /> <span v-if=\"isFieldInvalid('email')\" class=\"checkout-field__error\">{{ getFieldError('email') }}</span> </label> </div> <div class=\"checkout-grid checkout-grid--three\"> <label :class=\"['checkout-field', { 'checkout-field--error': isFieldInvalid('firstName') }]\"> <span class=\"checkout-field__label\"> Имя <span class=\"checkout-field__required\" aria-hidden=\"true\">*</span> </span> <input type=\"text\" :class=\"['checkout-input', { 'checkout-input--error': isFieldInvalid('firstName') }]\" v-model=\"form.recipient.firstName\" placeholder=\"Имя\" /> <span v-if=\"isFieldInvalid('firstName')\" class=\"checkout-field__error\">{{ getFieldError('firstName') }}</span> </label> <label :class=\"['checkout-field', { 'checkout-field--error': isFieldInvalid('lastName') }]\"> <span class=\"checkout-field__label\"> Фамилия <span class=\"checkout-field__required\" aria-hidden=\"true\">*</span> </span> <input type=\"text\" :class=\"['checkout-input', { 'checkout-input--error': isFieldInvalid('lastName') }]\" v-model=\"form.recipient.lastName\" placeholder=\"Фамилия\" /> <span v-if=\"isFieldInvalid('lastName')\" class=\"checkout-field__error\">{{ getFieldError('lastName') }}</span> </label> <label :class=\"['checkout-field', { 'checkout-field--error': isFieldInvalid('middleName') }]\"> <span class=\"checkout-field__label\"> Отчество <span class=\"checkout-field__required\" aria-hidden=\"true\">*</span> </span> <input type=\"text\" :class=\"['checkout-input', { 'checkout-input--error': isFieldInvalid('middleName') }]\" v-model=\"form.recipient.middleName\" placeholder=\"Отчество\" /> <span v-if=\"isFieldInvalid('middleName')\" class=\"checkout-field__error\">{{ getFieldError('middleName') }}</span> </label> </div> </section> </main> <aside class=\"checkout-page__sidebar\"> <div class=\"checkout-summary\"> <h2 class=\"checkout-summary__title\">Состав заказа</h2> <div v-if=\"!basketValidation.valid\" class=\"checkout-alert checkout-alert--error\"> <strong>Внимание!</strong> {{ basketValidation.error }} <template v-if=\"basketValidation.conflicts\"> <p style=\"margin-top: 8px; font-size: 13px;\"> Оформление заказа невозможно. Пожалуйста, удалите товары из разных городов. </p> </template> </div> <ul class=\"checkout-summary__list\"> <li v-for=\"item in cart.items\" :key=\"item.basketId\" class=\"checkout-summary__item\"> <div class=\"checkout-summary__item-name\">{{ item.name }}</div> <div class=\"checkout-summary__item-price\" v-html=\"item.sumPrint\"></div> </li> </ul> <dl class=\"checkout-summary__line\"> <dt>Товары, {{ cart.totalQty }} шт.</dt> <dd v-html=\"cart.totalSumPrint\"></dd> </dl> <dl class=\"checkout-summary__line\"> <dt>Оплата</dt> <dd>{{ paymentTitle }}</dd> </dl> <dl v-if=\"shouldDisplayDeliveryCost\" class=\"checkout-summary__line\"> <dt>Доставка</dt> <dd>{{ summaryDeliveryCostText }}</dd> </dl> <dl class=\"checkout-summary__total\"> <dt>Итого</dt> <dd v-html=\"summaryTotalHtml\"></dd> </dl> <div class=\"checkout-summary__consent\"> <label class=\"checkout-consent\" :class=\"{ 'checkout-consent--error': state.showValidationErrors && !form.agreement }\" :data-error=\"state.showValidationErrors && !form.agreement ? 'Обязательное поле' : ''\" > <input class=\"checkout-consent__input\" type=\"checkbox\" v-model=\"form.agreement\" @change=\"state.showValidationErrors = false\" /> <span class=\"checkout-consent__box\"></span> <span class=\"checkout-consent__text\"> Я подтверждаю свое совершеннолетие и соглашаюсь на <a href=\"/privacy-policy/\" target=\"_blank\" rel=\"noopener\">обработку персональных данных</a> в соответствии с <a href=\"/polzovatelskoe-soglashenie/\" target=\"_blank\" rel=\"noopener\">Пользовательским соглашением</a> и принимаю <a href=\"/dogovor-oferty/\" target=\"_blank\" rel=\"noopener\">договор оферты</a>. </span> </label> </div> <button type=\"button\" class=\"checkout-button checkout-button--primary\" @click=\"submitOrder\" > {{ state.isSubmitting ? 'Отправляем...' : 'Оформить заказ' }} </button> <p v-if=\"state.submitError\" class=\"checkout-error checkout-error--inline\">{{ state.submitError }}</p> </div> </aside> </div> <div v-else class=\"checkout-empty\"> <h1 class=\"checkout-empty__title\">Ваша корзина пуста</h1> <p class=\"checkout-empty__text\">Добавьте товары в корзину, чтобы оформить заказ.</p> <a class=\"checkout-button checkout-button--primary\" href=\"/catalog/\">Перейти в каталог</a> </div> <transition name=\"checkout-fade\"> <div v-if=\"state.showTerminalModal\" class=\"checkout-modal\" role=\"dialog\" aria-modal=\"true\"> <div class=\"checkout-modal__backdrop\" @click=\"closeTerminalModal\"></div> <div class=\"checkout-modal__dialog\"> <header class=\"checkout-modal__header\"> <h2 class=\"checkout-modal__title\">Выберите удобный для вас терминал</h2> <button type=\"button\" class=\"checkout-modal__close\" @click=\"closeTerminalModal\" aria-label=\"Закрыть\">×</button> </header> <div class=\"checkout-modal__body\"> <div class=\"checkout-modal__controls\"> <input type=\"text\" class=\"checkout-input checkout-modal__search\" placeholder=\"Поиск по адресу\" v-model=\"state.terminalSearch\" /> <div class=\"checkout-modal__view\"> <button type=\"button\" :class=\"['checkout-switch', { 'checkout-switch--active': state.terminalView === 'map' }]\" @click=\"setTerminalView('map')\" > Карта </button> <button type=\"button\" :class=\"['checkout-switch', { 'checkout-switch--active': state.terminalView === 'list' }]\" @click=\"setTerminalView('list')\" > Список </button> </div> </div> <div class=\"checkout-modal__content\" :class=\"{ 'checkout-modal__content--list-only': state.terminalView === 'list' }\"> <div v-if=\"state.terminalView === 'map'\" class=\"checkout-modal__map\"> <div ref=\"terminalMap\" class=\"checkout-modal__map-inner\" style=\"width: 100%; height: 400px\" ></div> <div v-if=\"state.isLoadingTerminals\" class=\"checkout-modal__loader\"> Загружаем карту... </div> <div v-if=\"state.terminalError\" class=\"checkout-modal__error\"> {{ state.terminalError }} </div> </div> <div class=\"checkout-modal__list\"> <div v-if=\"state.isLoadingTerminals\" class=\"checkout-modal__loader\">Загружаем список терминалов...</div> <template v-else-if=\"filteredTerminals.length\"> <div v-for=\"point in filteredTerminals\" :key=\"point.code\" class=\"checkout-terminal-card\" > <div class=\"checkout-terminal-card__content\"> <h3 class=\"checkout-terminal-card__title\">{{ point.name }}</h3> <p class=\"checkout-terminal-card__line\">{{ point.address }}</p> <p v-if=\"point.workTime\" class=\"checkout-terminal-card__note\"> {{ point.workTime }} </p> <p v-if=\"point.phones && point.phones.length\" class=\"checkout-terminal-card__note\"> {{ point.phones.join(', ') }} </p> </div> <button class=\"checkout-terminal-card__button\" @click=\"selectTerminal(point)\" > Выбрать </button> </div> </template> <div v-else class=\"checkout-modal__empty\"> Терминалы не найдены </div> </div> </div> </div> </div> </div> </transition> <transition name=\"checkout-fade\"> <div v-if=\"state.showSmsModal\" class=\"checkout-modal checkout-modal--sms\" role=\"dialog\" aria-modal=\"true\"> <div class=\"checkout-modal__backdrop\" @click=\"closeSmsModal\"></div> <div class=\"checkout-modal__dialog checkout-modal__dialog--narrow\"> <header class=\"checkout-modal__header\"> <h2 class=\"checkout-modal__title\">Подтверждение номера телефона</h2> <button type=\"button\" class=\"checkout-modal__close\" @click=\"closeSmsModal\" aria-label=\"Закрыть\">×</button> </header> <div class=\"checkout-modal__body checkout-sms-modal\"> <p class=\"checkout-sms-modal__description\"> Мы отправили Вам код подтверждения на телефон {{ getSmsDisplayPhone() }} </p> <label class=\"checkout-field checkout-sms-modal__field\"> <span class=\"checkout-field__label\">Код из SMS</span> <input type=\"text\" class=\"checkout-input\" v-model=\"smsVerification.code\" maxlength=\"6\" inputmode=\"numeric\" pattern=\"[0-9]*\" placeholder=\"123456\" ref=\"smsCodeInput\" /> </label> <p v-if=\"smsVerification.status\" class=\"checkout-hint checkout-hint--success checkout-sms-modal__status\"> {{ smsVerification.status }} </p> <p v-if=\"smsVerification.error\" class=\"checkout-error checkout-sms-modal__error\">{{ smsVerification.error }}</p> <div class=\"checkout-sms-modal__actions\"> <button type=\"button\" class=\"checkout-button checkout-button--primary\" :disabled=\"smsVerification.isChecking || !canConfirmSms\" @click=\"handleSmsModalConfirm\" > {{ smsVerification.isChecking ? 'Проверяем...' : 'Продолжить' }} </button> <button type=\"button\" class=\"checkout-button checkout-button--outline\" @click=\"closeSmsModal\">Отменить</button> </div> <div class=\"checkout-sms-modal__resend\"> <button v-if=\"smsVerification.resendSeconds <= 0\" type=\"button\" class=\"checkout-link-button\" :disabled=\"smsVerification.isSending\" @click=\"handleSmsResend\" > {{ smsVerification.isSending ? 'Отправляем...' : smsVerification.isCodeSent ? 'Отправить код повторно' : 'Отправить код ещё раз' }} </button> <p v-else class=\"checkout-sms-modal__hint\"> Отправить ещё раз через {{ smsVerification.resendSeconds }} секунд </p> </div> </div> </div> </div> </transition> </div>",
    name: 'DevobOrder',
    data: function data() {
      return {
        cart: {
          items: [],
          totalQty: 0,
          totalSum: 0,
          totalSumPrint: '300 ₽'
        },
        user: {
          isAuthorized: false,
          phone: '',
          email: '',
          firstName: '',
          lastName: '',
          middleName: ''
        },
        deliveryMethods: [],
        originalDeliveryMethods: [],
        paymentMethods: [],
        pickupHints: [],
        settings: {},
        addressSuggestions: [],
        suggestionTimer: null,
        terminals: [],
        deliveryCostRequestId: 0,
        map: {
          instance: null,
          collection: null,
          loader: null,
          objectManager: null,
          terminalLookup: {},
          apiKey: '',
          api: null
        },
        basketValidation: {
          valid: true,
          error: '',
          conflicts: null,
          cityCode: null,
          cityName: ''
        },
        form: {
          addressQuery: '',
          selectedAddress: null,
          deliveryType: '',
          terminal: {},
          courier: {
            city: '',
            street: '',
            house: '',
            flat: '',
            comment: ''
          },
          paymentType: '',
          recipient: {
            phone: '',
            email: '',
            firstName: '',
            lastName: '',
            middleName: ''
          },
          agreement: false
        },
        smsVerification: {
          phone: '',
          isCodeSent: false,
          isVerified: false,
          isSending: false,
          isChecking: false,
          code: '',
          error: '',
          status: '',
          resendSeconds: 0,
          attemptsLeft: null,
          timerId: null
        },
        state: {
          isLoadingSuggestions: false,
          isLoadingTerminals: false,
          isLoadingDeliveryCost: false,
          isSubmitting: false,
          submitError: '',
          submitSuccess: '',
          orderId: null,
          orderAccountNumber: '',
          orderDate: '',
          terminalError: '',
          deliveryCostError: '',
          deliveryCost: {
            value: null,
            text: '',
            isCalculated: false
          },
          showTerminalModal: false,
          terminalView: 'list',
          terminalSearch: '',
          showValidationErrors: false,
          showSmsModal: false
        }
      };
    },
    computed: {
      hasItems: function hasItems() {
        return Array.isArray(this.cart.items) && this.cart.items.length > 0;
      },
      isOrderCompleted: function isOrderCompleted() {
        return Boolean(this.state.orderId || this.state.orderAccountNumber || this.state.submitSuccess);
      },
      addressPlaceholder: function addressPlaceholder() {
        return 'Например: Казань, Татарстан';
      },
      isAddressSelected: function isAddressSelected() {
        return Boolean(this.form.selectedAddress);
      },
      isTerminalAvailable: function isTerminalAvailable() {
        return Boolean(this.form.selectedAddress && this.form.selectedAddress.hasTerminals !== false);
      },
      requiresSmsVerification: function requiresSmsVerification() {
        return !(this.user && this.user.isAuthorized);
      },
      isPhoneLocked: function isPhoneLocked() {
        return Boolean(this.user && this.user.isAuthorized);
      },
      canRequestSms: function canRequestSms() {
        var _this = this;
        if (!this.requiresSmsVerification) {
          return false;
        }
        var requiredFields = ['phone', 'email', 'firstName', 'lastName', 'middleName'];
        return requiredFields.every(function (field) {
          return !_this.getFieldValidation(field).invalid;
        });
      },
      canConfirmSms: function canConfirmSms() {
        var code = (this.smsVerification.code || '').replace(/\D/g, '');
        return code.length >= 4;
      },
      paymentTitle: function paymentTitle() {
        var _this2 = this;
        var current = this.paymentMethods.find(function (method) {
          return method.code === _this2.form.paymentType;
        });
        return current ? current.title : 'Не выбран';
      },
      shouldDisplayDeliveryCost: function shouldDisplayDeliveryCost() {
        var deliveryState = this.state.deliveryCost || {};
        var deliveryType = this.form.deliveryType;
        var supportsCalculation = deliveryType === 'terminal' || deliveryType === 'courier';
        return supportsCalculation && deliveryState.isCalculated && !this.state.deliveryCostError && (deliveryState.text || typeof deliveryState.value === 'number');
      },
      summaryDeliveryCostText: function summaryDeliveryCostText() {
        if (!this.shouldDisplayDeliveryCost) {
          return '';
        }
        var deliveryState = this.state.deliveryCost || {};
        if (deliveryState.text) {
          return deliveryState.text;
        }
        if (typeof deliveryState.value === 'number' && Number.isFinite(deliveryState.value)) {
          return this.formatCurrency(deliveryState.value);
        }
        return '';
      },
      summaryTotalHtml: function summaryTotalHtml() {
        var baseSum = Number(this.cart.totalSum) || 0;
        if (!this.shouldDisplayDeliveryCost) {
          return this.cart.totalSumPrint || this.formatCurrencyHtml(baseSum);
        }
        var deliveryState = this.state.deliveryCost || {};
        var deliveryValue = typeof deliveryState.value === 'number' && Number.isFinite(deliveryState.value) ? deliveryState.value : 0;
        return this.formatCurrencyHtml(baseSum + deliveryValue);
      },
      orderNumber: function orderNumber() {
        var number = this.state.orderAccountNumber || (this.state.orderId ? String(this.state.orderId) : '');
        return number ? "\u2116 ".concat(number) : '—';
      },
      orderDateFormatted: function orderDateFormatted() {
        if (!this.state.orderDate) {
          return '';
        }
        var date = new Date(this.state.orderDate);
        if (Number.isNaN(date.getTime())) {
          return '';
        }
        return date.toLocaleDateString('ru-RU', {
          day: '2-digit',
          month: 'long',
          year: 'numeric'
        });
      },
      isFormValid: function isFormValid() {
        var _this3 = this;
        if (!this.form.agreement) {
          return false;
        }
        if (!this.form.deliveryType || !this.form.paymentType) {
          return false;
        }
        var requiredFields = ['phone', 'email', 'firstName', 'lastName', 'middleName'];
        if (requiredFields.some(function (field) {
          return _this3.getFieldValidation(field).invalid;
        })) {
          return false;
        }
        if (this.form.deliveryType === 'terminal') {
          return Boolean(this.form.terminal && this.form.terminal.code);
        }
        if (this.form.deliveryType === 'courier') {
          var courier = this.form.courier || {};
          return Boolean(courier.city && courier.street && courier.house);
        }
        if (!this.form.deliveryType || this.form.deliveryType === 'pickup') {
          return true;
        }
        return Boolean(this.form.selectedAddress);
      },
      isSubmitDisabled: function isSubmitDisabled() {
        return this.state.isSubmitting || !this.isFormValid;
      },
      filteredTerminals: function filteredTerminals() {
        var query = (this.state.terminalSearch || '').toLowerCase();
        if (!query) {
          return this.terminals;
        }
        return this.terminals.filter(function (point) {
          var searchPool = [point.name, point.address, point.workTime, Array.isArray(point.phones) ? point.phones.join(' ') : ''].filter(Boolean).join(' ').toLowerCase();
          return searchPool.indexOf(query) !== -1;
        });
      }
    },
    mounted: function mounted() {
      var _this4 = this;
      this.syncServerData();
      this.initializeFromServer();
      this.checkBasketCities();

      // Применяем маску телефона
      this.$nextTick(function () {
        var phoneInput = _this4.$el.querySelector('input[type="tel"]');
        if (phoneInput && window.PhoneMask) {
          window.PhoneMask.applyMask(phoneInput);
        }
      });
    },
    watch: {
      'form.recipient.phone': function formRecipientPhone(newValue) {
        if (!this.requiresSmsVerification) {
          return;
        }
        var normalized = this.normalizePhone(newValue);
        if (normalized !== this.smsVerification.phone) {
          this.resetSmsVerificationState();
          this.smsVerification.phone = normalized;
        }
      },
      'user.isAuthorized': function userIsAuthorized(newValue) {
        if (newValue) {
          this.smsVerification.isVerified = true;
          this.smsVerification.status = '';
          this.smsVerification.error = '';
          this.clearSmsTimer();
        }
      }
    },
    beforeUnmount: function beforeUnmount() {
      // Удаляем маску при удалении компонента
      var phoneInput = this.$el.querySelector('input[type="tel"]');
      if (phoneInput && window.PhoneMask) {
        window.PhoneMask.removeMask(phoneInput);
      }
      if (this.suggestionTimer) {
        clearTimeout(this.suggestionTimer);
        this.suggestionTimer = null;
      }
      this.clearSmsTimer();
      if (this.map && this.map.instance) {
        if (typeof this.map.instance.destroy === 'function') {
          this.map.instance.destroy();
        }
        this.map.instance = null;
        if (this.map.collection && typeof this.map.collection.removeAll === 'function') {
          this.map.collection.removeAll();
        }
        this.map.collection = null;
      }
    },
    methods: {
      getFieldValidation: function getFieldValidation(field) {
        var recipient = this.form.recipient || {};
        var getValue = function getValue(name) {
          return String(recipient[name] || '').trim();
        };
        switch (field) {
          case 'phone':
            {
              var normalized = this.getNormalizedPhone();
              if (!normalized) {
                return {
                  invalid: true,
                  message: 'Укажите номер телефона'
                };
              }
              if (normalized.length < 11) {
                return {
                  invalid: true,
                  message: 'Введите корректный номер телефона'
                };
              }
              return {
                invalid: false,
                message: ''
              };
            }
          case 'email':
            {
              var email = getValue('email');
              if (!email) {
                return {
                  invalid: true,
                  message: 'Укажите e-mail получателя'
                };
              }
              if (!this.isValidEmail(email)) {
                return {
                  invalid: true,
                  message: 'Укажите корректный e-mail получателя'
                };
              }
              return {
                invalid: false,
                message: ''
              };
            }
          case 'firstName':
            if (!getValue('firstName')) {
              return {
                invalid: true,
                message: 'Укажите имя получателя'
              };
            }
            return {
              invalid: false,
              message: ''
            };
          case 'lastName':
            if (!getValue('lastName')) {
              return {
                invalid: true,
                message: 'Укажите фамилию получателя'
              };
            }
            return {
              invalid: false,
              message: ''
            };
          case 'middleName':
            if (!getValue('middleName')) {
              return {
                invalid: true,
                message: 'Укажите отчество получателя'
              };
            }
            return {
              invalid: false,
              message: ''
            };
          default:
            return {
              invalid: false,
              message: ''
            };
        }
      },
      isFieldInvalid: function isFieldInvalid(field) {
        if (!this.state.showValidationErrors) {
          return false;
        }
        return this.getFieldValidation(field).invalid;
      },
      getFieldError: function getFieldError(field) {
        var result = this.getFieldValidation(field);
        return result.invalid ? result.message : '';
      },
      getSmsDisplayPhone: function getSmsDisplayPhone() {
        var recipient = this.form.recipient || {};
        if (recipient.phone) {
          return recipient.phone;
        }
        var source = this.smsVerification.phone || '';
        if (!source) {
          return '';
        }
        var digits = String(source).replace(/\D/g, '');
        if (window.DevobPhoneMask && typeof window.DevobPhoneMask.format === 'function') {
          return window.DevobPhoneMask.format(digits);
        }
        if (window.PhoneMask && typeof window.PhoneMask.format === 'function') {
          return window.PhoneMask.format(digits);
        }
        if (digits.length === 11) {
          return "+".concat(digits[0], " (").concat(digits.slice(1, 4), ") ").concat(digits.slice(4, 7), "-").concat(digits.slice(7, 9), "-").concat(digits.slice(9, 11));
        }
        return digits ? "+".concat(digits) : '';
      },
      normalizePhone: function normalizePhone(value) {
        var raw = String(value || '').replace(/\D/g, '');
        if (!raw) {
          return '';
        }
        var normalized = raw;
        if (normalized.charAt(0) === '8') {
          normalized = "7".concat(normalized.slice(1));
        }
        if (normalized.charAt(0) !== '7') {
          normalized = "7".concat(normalized);
        }
        return normalized.length > 11 ? normalized.slice(0, 11) : normalized;
      },
      getNormalizedPhone: function getNormalizedPhone() {
        if (this.user && this.user.isAuthorized && this.user.phone) {
          return this.normalizePhone(this.user.phone);
        }
        var phoneValue = this.form.recipient ? this.form.recipient.phone : '';
        if (window.PhoneMask && typeof window.PhoneMask.unmask === 'function') {
          return this.normalizePhone(window.PhoneMask.unmask(phoneValue));
        }
        return this.normalizePhone(phoneValue);
      },
      resetSmsVerificationState: function resetSmsVerificationState() {
        this.clearSmsTimer();
        this.smsVerification.isCodeSent = false;
        this.smsVerification.isVerified = Boolean(this.user && this.user.isAuthorized);
        this.smsVerification.isSending = false;
        this.smsVerification.isChecking = false;
        this.smsVerification.code = '';
        this.smsVerification.error = '';
        this.smsVerification.status = '';
        this.smsVerification.resendSeconds = 0;
        this.smsVerification.attemptsLeft = null;
        this.state.showSmsModal = false;
      },
      clearSmsTimer: function clearSmsTimer() {
        if (this.smsVerification.timerId) {
          clearInterval(this.smsVerification.timerId);
          this.smsVerification.timerId = null;
        }
      },
      startSmsTimer: function startSmsTimer(seconds) {
        var _this5 = this;
        this.clearSmsTimer();
        var remaining = Number(seconds) || 0;
        this.smsVerification.resendSeconds = remaining;
        if (remaining <= 0) {
          return;
        }
        this.smsVerification.timerId = setInterval(function () {
          remaining -= 1;
          if (remaining <= 0) {
            _this5.smsVerification.resendSeconds = 0;
            _this5.clearSmsTimer();
          } else {
            _this5.smsVerification.resendSeconds = remaining;
          }
        }, 1000);
      },
      isValidEmail: function isValidEmail(value) {
        var email = String(value || '').trim();
        if (!email) {
          return false;
        }
        var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
      },
      requestSmsCode: function requestSmsCode() {
        var _this6 = this;
        return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
          var requiredFields, invalidField, phone, recipient, success, response, data, retryAfter, _t;
          return _regenerator().w(function (_context) {
            while (1) switch (_context.p = _context.n) {
              case 0:
                if (!(!_this6.requiresSmsVerification || _this6.smsVerification.isSending)) {
                  _context.n = 1;
                  break;
                }
                return _context.a(2, false);
              case 1:
                requiredFields = ['phone', 'email', 'firstName', 'lastName', 'middleName'];
                invalidField = requiredFields.find(function (field) {
                  return _this6.getFieldValidation(field).invalid;
                });
                if (!invalidField) {
                  _context.n = 2;
                  break;
                }
                _this6.smsVerification.error = _this6.getFieldValidation(invalidField).message;
                return _context.a(2, false);
              case 2:
                phone = _this6.getNormalizedPhone();
                if (!(!phone || phone.length < 11)) {
                  _context.n = 3;
                  break;
                }
                _this6.smsVerification.error = 'Введите корректный номер телефона';
                return _context.a(2, false);
              case 3:
                if (!(!window.BX || !BX.ajax || !BX.ajax.runComponentAction)) {
                  _context.n = 4;
                  break;
                }
                console.error('BX.ajax.runComponentAction не найден');
                _this6.smsVerification.error = 'Сервис подтверждения временно недоступен';
                return _context.a(2, false);
              case 4:
                recipient = _this6.form.recipient || {};
                _this6.smsVerification.isSending = true;
                _this6.smsVerification.error = '';
                _this6.smsVerification.status = '';
                _this6.smsVerification.code = '';
                success = false;
                _context.p = 5;
                _context.n = 6;
                return BX.ajax.runComponentAction('devob:order', 'sendSmsCode', {
                  mode: 'class',
                  data: {
                    phone: phone,
                    recipient: {
                      firstName: recipient.firstName,
                      lastName: recipient.lastName,
                      middleName: recipient.middleName,
                      email: recipient.email
                    }
                  }
                });
              case 6:
                response = _context.v;
                data = response && response.data ? response.data : {};
                if (!data.success) {
                  _this6.smsVerification.error = data.error || 'Не удалось отправить SMS. Попробуйте позже';
                  if (typeof data.retryAfter === 'number') {
                    _this6.startSmsTimer(data.retryAfter);
                  }
                } else {
                  _this6.smsVerification.isCodeSent = true;
                  _this6.smsVerification.status = data.message || 'Код отправлен на указанный номер';
                  _this6.smsVerification.phone = phone;
                  retryAfter = typeof data.retryAfter === 'number' ? data.retryAfter : 0;
                  _this6.startSmsTimer(retryAfter);
                  success = true;
                }
                _context.n = 8;
                break;
              case 7:
                _context.p = 7;
                _t = _context.v;
                console.error('Ошибка отправки SMS кода', _t);
                _this6.smsVerification.error = 'Не удалось отправить SMS. Попробуйте позже';
              case 8:
                _context.p = 8;
                _this6.smsVerification.isSending = false;
                return _context.f(8);
              case 9:
                return _context.a(2, success);
            }
          }, _callee, null, [[5, 7, 8, 9]]);
        }))();
      },
      confirmSmsCode: function confirmSmsCode() {
        var _this7 = this;
        return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
          var phone, code, success, payload, response, data, _t2;
          return _regenerator().w(function (_context2) {
            while (1) switch (_context2.p = _context2.n) {
              case 0:
                if (!(!_this7.requiresSmsVerification || _this7.smsVerification.isChecking || !_this7.canConfirmSms)) {
                  _context2.n = 1;
                  break;
                }
                return _context2.a(2, false);
              case 1:
                phone = _this7.smsVerification.phone || _this7.getNormalizedPhone();
                code = (_this7.smsVerification.code || '').replace(/\D/g, '');
                if (!(!phone || phone.length < 11)) {
                  _context2.n = 2;
                  break;
                }
                _this7.smsVerification.error = 'Введите корректный номер телефона';
                return _context2.a(2, false);
              case 2:
                if (code) {
                  _context2.n = 3;
                  break;
                }
                _this7.smsVerification.error = 'Введите код из SMS';
                return _context2.a(2, false);
              case 3:
                if (!(!window.BX || !BX.ajax || !BX.ajax.runComponentAction)) {
                  _context2.n = 4;
                  break;
                }
                console.error('BX.ajax.runComponentAction не найден');
                _this7.smsVerification.error = 'Сервис подтверждения временно недоступен';
                return _context2.a(2, false);
              case 4:
                _this7.smsVerification.isChecking = true;
                _this7.smsVerification.error = '';
                _this7.smsVerification.status = '';
                success = false;
                _context2.p = 5;
                payload = {
                  phone: phone,
                  code: code,
                  recipient: {
                    firstName: _this7.form.recipient.firstName,
                    lastName: _this7.form.recipient.lastName,
                    middleName: _this7.form.recipient.middleName,
                    email: _this7.form.recipient.email
                  }
                };
                _context2.n = 6;
                return BX.ajax.runComponentAction('devob:order', 'verifySmsCode', {
                  mode: 'class',
                  data: payload
                });
              case 6:
                response = _context2.v;
                data = response && response.data ? response.data : {};
                if (!data.success) {
                  _this7.smsVerification.error = data.error || 'Не удалось подтвердить код';
                  if (typeof data.attemptsLeft === 'number') {
                    _this7.smsVerification.attemptsLeft = data.attemptsLeft;
                    if (data.attemptsLeft >= 0) {
                      _this7.smsVerification.status = "\u041E\u0441\u0442\u0430\u043B\u043E\u0441\u044C \u043F\u043E\u043F\u044B\u0442\u043E\u043A: ".concat(data.attemptsLeft);
                    }
                  }
                } else {
                  _this7.smsVerification.isVerified = true;
                  _this7.smsVerification.status = data.message || 'Телефон успешно подтверждён';
                  _this7.smsVerification.error = '';
                  _this7.smsVerification.attemptsLeft = null;
                  _this7.clearSmsTimer();
                  if (data.phone) {
                    _this7.smsVerification.phone = data.phone;
                  }
                  if (data.authorized) {
                    _this7.user.isAuthorized = true;
                  }
                  success = true;
                }
                _context2.n = 8;
                break;
              case 7:
                _context2.p = 7;
                _t2 = _context2.v;
                console.error('Ошибка подтверждения SMS кода', _t2);
                _this7.smsVerification.error = 'Не удалось подтвердить код. Попробуйте позже';
              case 8:
                _context2.p = 8;
                _this7.smsVerification.isChecking = false;
                return _context2.f(8);
              case 9:
                return _context2.a(2, success);
            }
          }, _callee2, null, [[5, 7, 8, 9]]);
        }))();
      },
      openSmsModal: function openSmsModal() {
        var _this8 = this;
        this.state.showSmsModal = true;
        this.$nextTick(function () {
          var input = _this8.$refs.smsCodeInput;
          if (input && typeof input.focus === 'function') {
            input.focus();
          }
        });
      },
      closeSmsModal: function closeSmsModal() {
        this.state.showSmsModal = false;
      },
      handleSmsModalConfirm: function handleSmsModalConfirm() {
        var _this9 = this;
        return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3() {
          var success;
          return _regenerator().w(function (_context3) {
            while (1) switch (_context3.n) {
              case 0:
                if (!_this9.smsVerification.isChecking) {
                  _context3.n = 1;
                  break;
                }
                return _context3.a(2);
              case 1:
                _context3.n = 2;
                return _this9.confirmSmsCode();
              case 2:
                success = _context3.v;
                if (success) {
                  _this9.state.showSmsModal = false;
                  _this9.state.submitError = '';
                  _this9.submitOrder();
                }
              case 3:
                return _context3.a(2);
            }
          }, _callee3);
        }))();
      },
      handleSmsResend: function handleSmsResend() {
        var _this0 = this;
        return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
          return _regenerator().w(function (_context4) {
            while (1) switch (_context4.n) {
              case 0:
                if (!(_this0.smsVerification.isSending || _this0.smsVerification.resendSeconds > 0)) {
                  _context4.n = 1;
                  break;
                }
                return _context4.a(2);
              case 1:
                _context4.n = 2;
                return _this0.requestSmsCode();
              case 2:
                _this0.$nextTick(function () {
                  var input = _this0.$refs.smsCodeInput;
                  if (input && typeof input.focus === 'function') {
                    input.focus();
                  }
                });
              case 3:
                return _context4.a(2);
            }
          }, _callee4);
        }))();
      },
      formatCurrency: function formatCurrency(amount) {
        if (typeof amount !== 'number' || !Number.isFinite(amount)) {
          return '';
        }
        return new Intl.NumberFormat('ru-RU', {
          style: 'currency',
          currency: 'RUB',
          minimumFractionDigits: 0,
          maximumFractionDigits: 2
        }).format(amount);
      },
      isDeliveryMethodDisabled: function isDeliveryMethodDisabled(method) {
        if (!this.isAddressSelected) {
          return true;
        }
        if (!method || !method.code) {
          return false;
        }
        if (method.code === 'terminal' && !this.isTerminalAvailable) {
          return true;
        }
        return false;
      },
      getFallbackDeliveryMethodCode: function getFallbackDeliveryMethodCode() {
        var _this1 = this;
        if (!Array.isArray(this.deliveryMethods) || !this.deliveryMethods.length) {
          return '';
        }
        var preferredCodes = ['courier', 'pickup'];
        var _loop = function _loop() {
            var code = _preferredCodes[_i];
            if (_this1.deliveryMethods.some(function (method) {
              return method.code === code;
            })) {
              return {
                v: code
              };
            }
          },
          _ret;
        for (var _i = 0, _preferredCodes = preferredCodes; _i < _preferredCodes.length; _i++) {
          _ret = _loop();
          if (_ret) return _ret.v;
        }
        var fallback = this.deliveryMethods.find(function (method) {
          return method.code !== 'terminal';
        });
        return fallback ? fallback.code : '';
      },
      handleTerminalNotAvailableForAddress: function handleTerminalNotAvailableForAddress() {
        var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var _options$updateCourie = options.updateCourierCost,
          updateCourierCost = _options$updateCourie === void 0 ? true : _options$updateCourie;
        this.state.deliveryCostError = '';
        this.state.terminalError = 'Терминалы СДЭК для выбранного города не найдены';
        this.state.showTerminalModal = false;
        this.form.terminal = {};
        this.terminals = [];
        this.state.isLoadingTerminals = false;
        var fallbackCode = this.getFallbackDeliveryMethodCode();
        if (fallbackCode) {
          this.form.deliveryType = fallbackCode;
          if (fallbackCode === 'courier') {
            this.clearCourierDeliveryCostState();
            if (updateCourierCost) {
              this.updateCourierDeliveryCost();
            }
          } else {
            this.clearCourierDeliveryCostState();
          }
        } else {
          this.form.deliveryType = '';
          this.clearCourierDeliveryCostState();
        }
        return fallbackCode;
      },
      formatCurrencyHtml: function formatCurrencyHtml(amount) {
        var formatted = this.formatCurrency(amount);
        return formatted ? formatted.replace(/\u00A0/g, '&nbsp;') : '';
      },
      setDeliveryCost: function setDeliveryCost(value, text) {
        var isCalculated = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
        var numericValue = typeof value === 'number' && Number.isFinite(value) ? value : null;
        this.state.deliveryCost = {
          value: numericValue,
          text: text || '',
          isCalculated: Boolean(isCalculated)
        };
      },
      resetDeliveryCost: function resetDeliveryCost() {
        this.setDeliveryCost(null, '', false);
      },
      /**
       * Проверяет, что все товары в корзине из одного города
       */
      checkBasketCities: function checkBasketCities() {
        var _this10 = this;
        return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5() {
          var response, data, _t3;
          return _regenerator().w(function (_context5) {
            while (1) switch (_context5.p = _context5.n) {
              case 0:
                if (!(!window.BX || !BX.ajax || !BX.ajax.runComponentAction)) {
                  _context5.n = 1;
                  break;
                }
                console.error('BX.ajax.runComponentAction не найден');
                return _context5.a(2, false);
              case 1:
                _context5.p = 1;
                _context5.n = 2;
                return BX.ajax.runComponentAction('devob:order', 'getBasketCitiesInfo', {
                  mode: 'class',
                  data: {}
                });
              case 2:
                response = _context5.v;
                data = response && response.data ? response.data : {};
                if (data.valid) {
                  _context5.n = 3;
                  break;
                }
                _this10.basketValidation.valid = false;
                _this10.basketValidation.error = data.error || 'В корзине товары из разных городов';
                _this10.basketValidation.conflicts = data.conflicts || null;
                return _context5.a(2, false);
              case 3:
                _this10.basketValidation.valid = true;
                _this10.basketValidation.error = '';
                _this10.basketValidation.conflicts = null;
                _this10.basketValidation.cityCode = data.cityCode || null;
                _this10.basketValidation.cityName = data.cityName || '';
                return _context5.a(2, true);
              case 4:
                _context5.p = 4;
                _t3 = _context5.v;
                console.error('Ошибка проверки городов корзины:', _t3);
                _this10.basketValidation.valid = false;
                _this10.basketValidation.error = 'Не удалось проверить корзину';
                return _context5.a(2, false);
            }
          }, _callee5, null, [[1, 4]]);
        }))();
      },
      syncServerData: function syncServerData() {
        if (Array.isArray(this.delivery_methods) && this.delivery_methods.length) {
          var normalized = this.delivery_methods.map(function (method) {
            return _objectSpread(_objectSpread({}, method), {}, {
              loading: false
            });
          });
          if (!this.originalDeliveryMethods.length) {
            this.originalDeliveryMethods = normalized.map(function (method) {
              return _objectSpread({}, method);
            });
          }
          if (!this.deliveryMethods.length) {
            this.deliveryMethods = normalized.map(function (method) {
              return _objectSpread({}, method);
            });
          }
        }
        if (Array.isArray(this.payment_methods) && !this.paymentMethods.length) {
          this.paymentMethods = this.payment_methods.slice();
        }
        if (Array.isArray(this.pickup_hints) && !this.pickupHints.length) {
          this.pickupHints = this.pickup_hints.slice();
        }
      },
      updateDeliveryMethod: function updateDeliveryMethod(code) {
        var patch = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        if (!Array.isArray(this.deliveryMethods) || !this.deliveryMethods.length) {
          return;
        }
        var index = this.deliveryMethods.findIndex(function (method) {
          return method.code === code;
        });
        if (index === -1) {
          return;
        }
        var updated = Object.assign({}, this.deliveryMethods[index], patch);
        this.deliveryMethods.splice(index, 1, updated);
      },
      getOriginalDeliveryMethod: function getOriginalDeliveryMethod(code) {
        if (!Array.isArray(this.originalDeliveryMethods)) {
          return undefined;
        }
        return this.originalDeliveryMethods.find(function (method) {
          return method.code === code;
        });
      },
      resetTerminalDeliveryMethod: function resetTerminalDeliveryMethod() {
        this.resetDeliveryCost();
        var original = this.getOriginalDeliveryMethod('terminal');
        if (original) {
          this.updateDeliveryMethod('terminal', {
            price: original.price || '',
            loading: false
          });
        } else {
          this.updateDeliveryMethod('terminal', {
            price: '',
            loading: false
          });
        }
      },
      resetCourierDeliveryMethod: function resetCourierDeliveryMethod() {
        var original = this.getOriginalDeliveryMethod('courier');
        if (original) {
          this.updateDeliveryMethod('courier', {
            price: original.price || '',
            loading: false
          });
        } else {
          this.updateDeliveryMethod('courier', {
            price: '',
            loading: false
          });
        }
      },
      clearTerminalDeliveryCostState: function clearTerminalDeliveryCostState() {
        var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var _options$resetPrice = options.resetPrice,
          resetPrice = _options$resetPrice === void 0 ? true : _options$resetPrice;
        this.deliveryCostRequestId += 1;
        this.state.isLoadingDeliveryCost = false;
        this.state.deliveryCostError = '';
        this.resetDeliveryCost();
        if (resetPrice) {
          this.resetTerminalDeliveryMethod();
        } else {
          this.updateDeliveryMethod('terminal', {
            loading: false
          });
        }
      },
      clearCourierDeliveryCostState: function clearCourierDeliveryCostState() {
        var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var _options$resetPrice2 = options.resetPrice,
          resetPrice = _options$resetPrice2 === void 0 ? true : _options$resetPrice2;
        this.deliveryCostRequestId += 1;
        this.state.isLoadingDeliveryCost = false;
        this.state.deliveryCostError = '';
        this.resetDeliveryCost();
        if (resetPrice) {
          this.resetCourierDeliveryMethod();
        } else {
          this.updateDeliveryMethod('courier', {
            loading: false
          });
        }
      },
      updateTerminalDeliveryCost: function updateTerminalDeliveryCost() {
        var _this11 = this;
        var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var _options$force = options.force,
          force = _options$force === void 0 ? false : _options$force;
        if (!force && this.form.deliveryType !== 'terminal') {
          return;
        }
        var address = this.form.selectedAddress;
        if (!address) {
          this.clearTerminalDeliveryCostState();
          this.state.deliveryCostError = 'Сначала выберите адрес доставки';
          return;
        }
        if (address.hasTerminals === false) {
          this.clearTerminalDeliveryCostState();
          this.state.deliveryCostError = 'Для выбранного города нет терминалов СДЭК';
          return;
        }
        if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
          console.error('BX.ajax.runComponentAction не найден');
          this.clearTerminalDeliveryCostState(false);
          this.state.deliveryCostError = 'Не удалось рассчитать стоимость доставки';
          return;
        }
        var requestId = ++this.deliveryCostRequestId;
        this.state.isLoadingDeliveryCost = true;
        this.state.deliveryCostError = '';
        this.resetDeliveryCost();
        this.updateDeliveryMethod('terminal', {
          loading: true,
          price: ''
        });
        var finalizeRequest = function finalizeRequest() {
          if (requestId === _this11.deliveryCostRequestId) {
            _this11.state.isLoadingDeliveryCost = false;
            _this11.updateDeliveryMethod('terminal', {
              loading: false
            });
          }
        };
        BX.ajax.runComponentAction('devob:order', 'calculateTerminalDelivery', {
          mode: 'class',
          data: {
            address: address
          }
        }).then(function (response) {
          if (requestId !== _this11.deliveryCostRequestId) {
            return;
          }
          var data = response && response.data ? response.data : {};
          if (!data.success) {
            var errorMessage = data.error || 'Не удалось рассчитать стоимость доставки';
            _this11.state.deliveryCostError = errorMessage;
            _this11.resetTerminalDeliveryMethod();
            finalizeRequest();
            return;
          }
          var priceText = '';
          if (data.pricePrint) {
            var textarea = document.createElement('textarea');
            textarea.innerHTML = data.pricePrint;
            priceText = textarea.value;
          } else if (typeof data.price !== 'undefined' && data.price !== null) {
            priceText = String(data.price) + ' ₽';
          }
          var rawPrice = null;
          if (typeof data.price === 'number') {
            rawPrice = data.price;
          } else if (typeof data.price === 'string' && data.price.trim() !== '') {
            rawPrice = Number.parseFloat(data.price.replace(',', '.'));
          }
          var priceValue = typeof rawPrice === 'number' && Number.isFinite(rawPrice) ? rawPrice : null;
          var finalPriceText = priceText || (priceValue !== null ? _this11.formatCurrency(priceValue) : '');
          _this11.updateDeliveryMethod('terminal', {
            loading: false,
            price: finalPriceText
          });
          _this11.setDeliveryCost(priceValue, finalPriceText, Boolean(finalPriceText || priceValue !== null));
          _this11.state.deliveryCostError = '';
          finalizeRequest();
        }).catch(function (error) {
          if (requestId !== _this11.deliveryCostRequestId) {
            return;
          }
          console.error('Ошибка расчёта стоимости терминальной доставки', error);
          _this11.state.deliveryCostError = 'Не удалось рассчитать стоимость доставки';
          _this11.resetTerminalDeliveryMethod();
          finalizeRequest();
        });
      },
      updateCourierDeliveryCost: function updateCourierDeliveryCost() {
        var _this12 = this;
        if (this.form.deliveryType !== 'courier') {
          return;
        }
        var address = this.form.selectedAddress;
        if (!address) {
          this.clearCourierDeliveryCostState();
          this.state.deliveryCostError = 'Сначала выберите адрес доставки';
          return;
        }
        if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
          console.error('BX.ajax.runComponentAction не найден');
          this.clearCourierDeliveryCostState(false);
          this.state.deliveryCostError = 'Не удалось рассчитать стоимость доставки';
          return;
        }
        var requestId = ++this.deliveryCostRequestId;
        this.state.isLoadingDeliveryCost = true;
        this.state.deliveryCostError = '';
        this.resetDeliveryCost();
        this.updateDeliveryMethod('courier', {
          loading: true,
          price: ''
        });
        var finalizeRequest = function finalizeRequest() {
          if (requestId === _this12.deliveryCostRequestId) {
            _this12.state.isLoadingDeliveryCost = false;
            _this12.updateDeliveryMethod('courier', {
              loading: false
            });
          }
        };
        BX.ajax.runComponentAction('devob:order', 'calculateCourierDelivery', {
          mode: 'class',
          data: {
            address: address
          }
        }).then(function (response) {
          if (requestId !== _this12.deliveryCostRequestId) {
            return;
          }
          var data = response && response.data ? response.data : {};
          if (!data.success) {
            var errorMessage = data.error || 'Не удалось рассчитать стоимость доставки';
            _this12.state.deliveryCostError = errorMessage;
            _this12.resetCourierDeliveryMethod();
            finalizeRequest();
            return;
          }
          var priceText = '';
          if (data.pricePrint) {
            var textarea = document.createElement('textarea');
            textarea.innerHTML = data.pricePrint;
            priceText = textarea.value;
          } else if (typeof data.price !== 'undefined' && data.price !== null) {
            priceText = String(data.price) + ' ₽';
          }
          var rawPrice = null;
          if (typeof data.price === 'number') {
            rawPrice = data.price;
          } else if (typeof data.price === 'string' && data.price.trim() !== '') {
            rawPrice = Number.parseFloat(data.price.replace(',', '.'));
          }
          var priceValue = typeof rawPrice === 'number' && Number.isFinite(rawPrice) ? rawPrice : null;
          var finalPriceText = priceText || (priceValue !== null ? _this12.formatCurrency(priceValue) : '');
          _this12.updateDeliveryMethod('courier', {
            loading: false,
            price: finalPriceText
          });
          _this12.setDeliveryCost(priceValue, finalPriceText, Boolean(finalPriceText || priceValue !== null));
          _this12.state.deliveryCostError = '';
          finalizeRequest();
        }).catch(function (error) {
          if (requestId !== _this12.deliveryCostRequestId) {
            return;
          }
          console.error('Ошибка расчёта стоимости курьерской доставки', error);
          _this12.state.deliveryCostError = 'Не удалось рассчитать стоимость доставки';
          _this12.resetCourierDeliveryMethod();
          finalizeRequest();
        });
      },
      initializeFromServer: function initializeFromServer() {
        var _this13 = this;
        if (this.user) {
          this.form.recipient = Object.assign({}, this.form.recipient, {
            phone: this.user.phone || '',
            email: this.user.email || '',
            firstName: this.user.firstName || '',
            lastName: this.user.lastName || '',
            middleName: this.user.middleName || ''
          });
        }
        if (this.paymentMethods && this.paymentMethods.length && !this.form.paymentType) {
          this.form.paymentType = this.paymentMethods[0].code;
        }
        if (!this.form.selectedAddress) {
          this.form.deliveryType = '';
        } else if (this.deliveryMethods && this.deliveryMethods.length && !this.form.deliveryType) {
          this.form.deliveryType = this.deliveryMethods[0].code;
        }
        if (this.form.selectedAddress && this.form.selectedAddress.value) {
          this.form.addressQuery = this.form.selectedAddress.value;
        }
        if (this.form.deliveryType === 'terminal' && this.form.selectedAddress) {
          if (this.form.selectedAddress.hasTerminals === false) {
            this.handleTerminalNotAvailableForAddress({
              updateCourierCost: false
            });
          } else {
            this.$nextTick(function () {
              _this13.updateTerminalDeliveryCost();
            });
          }
        }
        if (this.form.deliveryType === 'courier' && this.form.selectedAddress) {
          this.$nextTick(function () {
            _this13.updateCourierDeliveryCost();
          });
        }
        this.resetSmsVerificationState();
        this.smsVerification.phone = this.getNormalizedPhone();
      },
      onAddressInput: function onAddressInput() {
        var _this14 = this;
        var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        var query = (this.form.addressQuery || '').trim();
        if (!query) {
          if (event && event.type === 'input') {
            this.clearAddress();
            return;
          }
          this.addressSuggestions = [];
          if (this.suggestionTimer) {
            clearTimeout(this.suggestionTimer);
            this.suggestionTimer = null;
          }
          this.state.isLoadingSuggestions = false;
          return;
        }
        if (this.suggestionTimer) {
          clearTimeout(this.suggestionTimer);
        }
        this.state.isLoadingSuggestions = true;
        this.suggestionTimer = setTimeout(function () {
          _this14.fetchAddressSuggestions(query);
        }, 200);
      },
      fetchAddressSuggestions: function fetchAddressSuggestions(query) {
        var _this15 = this;
        if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
          console.error('BX.ajax.runComponentAction не найден');
          this.state.isLoadingSuggestions = false;
          return;
        }
        var finalizeSuggestions = function finalizeSuggestions() {
          _this15.state.isLoadingSuggestions = false;
        };
        var limit = 10;
        BX.ajax.runComponentAction('devob:order', 'suggestAddress', {
          mode: 'class',
          data: {
            query: query,
            limit: limit
          }
        }).then(function (response) {
          var data = response && response.data ? response.data : {};
          if (!data.success) {
            _this15.addressSuggestions = [];
            if (data.error) {
              console.warn(data.error);
            }
            finalizeSuggestions();
            return;
          }
          _this15.addressSuggestions = Array.isArray(data.items) ? data.items : [];
          finalizeSuggestions();
        }).catch(function (error) {
          console.error('Ошибка загрузки подсказок адреса', error);
          finalizeSuggestions();
        });
      },
      selectAddress: function selectAddress(item) {
        this.form.selectedAddress = item;
        this.form.addressQuery = item.value;
        this.addressSuggestions = [];
        this.state.isLoadingSuggestions = false;
        if (this.form.deliveryType === 'terminal') {
          this.form.terminal = {};
          this.terminals = [];
          this.state.terminalError = '';
          this.clearTerminalDeliveryCostState();
          if (item && item.hasTerminals) {
            this.updateTerminalDeliveryCost();
            this.loadTerminals();
          } else {
            this.handleTerminalNotAvailableForAddress();
          }
        } else if (this.form.deliveryType === 'courier') {
          this.clearCourierDeliveryCostState();
          this.updateCourierDeliveryCost();
        }
      },
      clearAddress: function clearAddress() {
        if (this.suggestionTimer) {
          clearTimeout(this.suggestionTimer);
          this.suggestionTimer = null;
        }
        this.form.addressQuery = '';
        this.form.selectedAddress = null;
        this.addressSuggestions = [];
        this.form.terminal = {};
        this.terminals = [];
        this.form.courier = {
          city: '',
          street: '',
          house: '',
          flat: '',
          comment: ''
        };
        this.form.deliveryType = '';
        this.state.isLoadingSuggestions = false;
        this.state.terminalError = '';
        this.clearTerminalDeliveryCostState();
        this.clearCourierDeliveryCostState();
        this.state.showTerminalModal = false;
      },
      selectDelivery: function selectDelivery(code) {
        if (!this.form.selectedAddress) {
          return;
        }
        if (code === 'terminal' && !this.isTerminalAvailable) {
          this.state.deliveryCostError = '';
          this.state.terminalError = 'Терминалы СДЭК для выбранного города не найдены';
          this.state.showTerminalModal = false;
          return;
        }
        this.form.deliveryType = code;
        this.state.terminalError = '';
        if (code === 'terminal') {
          this.clearCourierDeliveryCostState();
        } else if (code === 'courier') {
          this.form.terminal = {};
          this.state.showTerminalModal = false;
          this.clearTerminalDeliveryCostState();
          this.clearCourierDeliveryCostState();
          this.updateCourierDeliveryCost();
          return;
        } else {
          this.form.terminal = {};
          this.state.showTerminalModal = false;
          this.clearTerminalDeliveryCostState();
          this.clearCourierDeliveryCostState();
          return;
        }
        this.clearTerminalDeliveryCostState();
        if (this.form.selectedAddress) {
          if (this.isTerminalAvailable) {
            this.updateTerminalDeliveryCost();
          } else {
            this.terminals = [];
            this.state.isLoadingTerminals = false;
            this.state.deliveryCostError = 'Для выбранного города нет терминалов СДЭК';
            this.state.terminalError = 'Терминалы СДЭК для выбранного города не найдены';
          }
        } else {
          this.terminals = [];
          this.state.isLoadingTerminals = false;
          this.state.deliveryCostError = 'Сначала выберите адрес доставки';
          this.state.terminalError = 'Сначала выберите адрес доставки';
        }
        this.openTerminalModal();
      },
      openTerminalModal: function openTerminalModal() {
        var _this16 = this;
        if (!this.form.selectedAddress) {
          this.state.showTerminalModal = true;
          if (!this.state.terminalError) {
            this.state.terminalError = 'Сначала выберите адрес доставки';
          }
          return;
        }
        if (this.form.selectedAddress.hasTerminals === false) {
          this.terminals = [];
          this.state.isLoadingTerminals = false;
          this.state.showTerminalModal = true;
          if (!this.state.terminalError) {
            this.state.terminalError = 'Терминалы СДЭК для выбранного города не найдены';
          }
          return;
        }
        this.state.terminalError = '';
        this.state.showTerminalModal = true;
        this.loadTerminals().then(function () {
          _this16.$nextTick(function () {
            // this.ensureMapReady();
          });
        });
      },
      closeTerminalModal: function closeTerminalModal() {
        this.state.showTerminalModal = false;
      },
      loadTerminals: function loadTerminals() {
        var _this17 = this;
        return new Promise(function (resolve, reject) {
          if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
            console.error('BX.ajax.runComponentAction не найден');
            reject(new Error('BX не найден'));
            return;
          }
          _this17.state.isLoadingTerminals = true;
          _this17.state.terminalError = '';
          BX.ajax.runComponentAction('devob:order', 'loadTerminals', {
            mode: 'class',
            data: {
              address: _this17.form.selectedAddress
            }
          }).then(function (response) {
            var data = response && response.data ? response.data : {};
            if (!data.success) {
              _this17.terminals = [];
              _this17.state.terminalError = data.error || 'Не удалось получить список терминалов';
              _this17.state.isLoadingTerminals = false;
              reject(new Error(_this17.state.terminalError));
              return;
            }
            _this17.terminals = Array.isArray(data.items) ? data.items : [];
            if (data.yandexApiKey) {
              _this17.map.apiKey = data.yandexApiKey;
            }
            console.log('Загруженные терминалы:', _this17.terminals);
            if (!_this17.terminals.length) {
              _this17.state.terminalError = 'Терминалы СДЭК для выбранного города не найдены';
              _this17.state.isLoadingTerminals = false;
              reject(new Error(_this17.state.terminalError));
              return;
            }
            _this17.state.isLoadingTerminals = false;
            resolve(_this17.terminals); // ✓ Вернули терминалы!
          }).catch(function (error) {
            console.error('Ошибка загрузки терминалов', error);
            _this17.state.terminalError = 'Не удалось получить список терминалов';
            _this17.state.isLoadingTerminals = false;
            reject(error);
          });
        });
      },
      selectTerminal: function selectTerminal(point) {
        var closeModal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
        this.form.terminal = Object.assign({}, point);
        if (closeModal) {
          this.closeTerminalModal();
        }
      },
      setTerminalView: function setTerminalView(view) {
        var _this18 = this;
        this.state.terminalView = view;
        if (view === 'map') {
          this.$nextTick(function () {
            // Проверяем, что терминалы уже загружены
            if (_this18.terminals && _this18.terminals.length > 0) {
              // this.ensureMapReady();
            } else {
              console.warn('Переключились на карту, но терминалы ещё не загружены');
            }
          });
        }
      },
      submitOrder: function submitOrder() {
        var _this19 = this;
        return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6() {
          var isBasketValid, smsWasSent, unmaskedPhone, payload, finalizeSubmit, _t4;
          return _regenerator().w(function (_context6) {
            while (1) switch (_context6.n) {
              case 0:
                _context6.n = 1;
                return _this19.checkBasketCities();
              case 1:
                isBasketValid = _context6.v;
                if (isBasketValid) {
                  _context6.n = 2;
                  break;
                }
                _this19.state.submitError = _this19.basketValidation.error || 'В корзине товары из разных городов';
                _this19.state.submitSuccess = '';
                _this19.state.orderId = null;
                _this19.state.orderAccountNumber = '';
                return _context6.a(2);
              case 2:
                if (!(!window.BX || !BX.ajax || !BX.ajax.runComponentAction)) {
                  _context6.n = 3;
                  break;
                }
                console.error('BX.ajax.runComponentAction не найден');
                _this19.state.orderId = null;
                _this19.state.orderAccountNumber = '';
                return _context6.a(2);
              case 3:
                if (_this19.isFormValid) {
                  _context6.n = 4;
                  break;
                }
                _this19.state.showValidationErrors = true;
                _this19.state.submitError = 'Пожалуйста, заполните обязательные поля и подтвердите согласие на обработку персональных данных';
                _this19.state.submitSuccess = '';
                _this19.state.orderId = null;
                _this19.state.orderAccountNumber = '';
                return _context6.a(2);
              case 4:
                _this19.state.showValidationErrors = false;
                if (!(_this19.requiresSmsVerification && !_this19.smsVerification.isVerified)) {
                  _context6.n = 7;
                  break;
                }
                _t4 = _this19.smsVerification.isCodeSent;
                if (_t4) {
                  _context6.n = 6;
                  break;
                }
                _context6.n = 5;
                return _this19.requestSmsCode();
              case 5:
                _t4 = _context6.v;
              case 6:
                smsWasSent = _t4;
                _this19.state.submitSuccess = '';
                _this19.state.orderId = null;
                _this19.state.orderAccountNumber = '';
                if (smsWasSent) {
                  _this19.state.submitError = '';
                  _this19.openSmsModal();
                } else {
                  _this19.state.submitError = _this19.smsVerification.error || 'Не удалось отправить SMS. Попробуйте позже';
                }
                return _context6.a(2);
              case 7:
                _this19.state.showSmsModal = false;
                _this19.state.isSubmitting = true;
                _this19.state.submitError = '';
                _this19.state.submitSuccess = '';
                _this19.state.orderId = null;
                _this19.state.orderAccountNumber = '';
                unmaskedPhone = window.PhoneMask && typeof window.PhoneMask.unmask === 'function' ? window.PhoneMask.unmask(_this19.form.recipient.phone) : window.DevobPhoneMask && typeof window.DevobPhoneMask.getDigits === 'function' ? window.DevobPhoneMask.getDigits(_this19.form.recipient.phone) : _this19.getNormalizedPhone();
                payload = {
                  deliveryType: _this19.form.deliveryType,
                  address: _this19.form.selectedAddress,
                  terminal: _this19.form.terminal,
                  courier: _this19.form.courier,
                  paymentType: _this19.form.paymentType,
                  recipient: _objectSpread(_objectSpread({}, _this19.form.recipient), {}, {
                    phone: unmaskedPhone // Отправляем чистый номер
                  })
                };
                finalizeSubmit = function finalizeSubmit() {
                  _this19.state.isSubmitting = false;
                };
                BX.ajax.runComponentAction('devob:order', 'createOrder', {
                  mode: 'class',
                  data: {
                    form: payload
                  }
                }).then(function (response) {
                  var data = response && response.data ? response.data : {};
                  if (!data.success) {
                    var errors = [];
                    if (Array.isArray(data.errors)) {
                      errors = data.errors;
                    } else if (data.error) {
                      errors = [data.error];
                    }
                    _this19.state.submitError = errors.join('. ');
                    _this19.state.orderId = null;
                    _this19.state.orderAccountNumber = '';
                    finalizeSubmit();
                    return;
                  }
                  _this19.state.submitSuccess = data.message || 'Заказ успешно оформлен';
                  _this19.state.orderId = data.orderId || null;
                  _this19.state.orderAccountNumber = data.accountNumber || '';
                  _this19.state.orderDate = data.orderDate || new Date().toISOString();
                  _this19.cart.items = [];
                  _this19.cart.totalQty = 0;
                  _this19.cart.totalSum = 0;
                  _this19.cart.totalSumPrint = '0 ₽';
                  _this19.basketValidation.valid = true;
                  _this19.basketValidation.error = '';
                  _this19.basketValidation.conflicts = null;
                  _this19.state.showTerminalModal = false;
                  if (window.BX && typeof BX.onCustomEvent === 'function') {
                    BX.onCustomEvent('OnBasketChange');
                  }

                  // Перенаправление на оплату
                  if (data.payment && data.payment.needRedirect && data.payment.url) {
                    // Даем пользователю время увидеть сообщение об успехе
                    setTimeout(function () {
                      window.location.href = data.payment.url;
                    }, 1500);
                  } else {
                    finalizeSubmit();
                  }
                }).catch(function (error) {
                  console.error('Ошибка оформления заказа', error);
                  _this19.state.submitError = 'Не удалось отправить заказ. Попробуйте позже';
                  _this19.state.orderId = null;
                  _this19.state.orderAccountNumber = '';
                  finalizeSubmit();
                });
              case 8:
                return _context6.a(2);
            }
          }, _callee6);
        }))();
      }
    }
  };
});