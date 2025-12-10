<template>
  <div class="checkout-page">
    <div v-if="isOrderCompleted" class="checkout-success">
      <div class="checkout-success__icon" aria-hidden="true">✓</div>
      <h1 class="checkout-success__title">{{ state.submitSuccess || 'Заказ успешно оформлен' }}</h1>
      <p class="checkout-success__subtitle">
        Мы отправили подтверждение заказа. Менеджер свяжется с вами при необходимости.
      </p>
      <dl class="checkout-success__details">
        <div class="checkout-success__details-item">
          <dt>Номер заказа</dt>
          <dd>{{ orderNumber }}</dd>
        </div>
        <div v-if="orderDateFormatted" class="checkout-success__details-item">
          <dt>Дата оформления</dt>
          <dd>{{ orderDateFormatted }}</dd>
        </div>
      </dl>
      <div class="checkout-success__actions">
        <a class="checkout-button checkout-button--primary" href="/personal/orders/">Перейти в мои заказы</a>
        <a class="checkout-button checkout-button--outline" href="/catalog/">Вернуться в каталог</a>
      </div>
    </div>

    <div class="checkout-page__layout" v-else-if="hasItems">
      <main class="checkout-page__main">
        <section class="checkout-section">
          <h1 class="checkout-section__title">1. Где и как вы хотите получить заказ</h1>

          <div class="checkout-block">
            <label class="checkout-label" for="checkout-address">Введите город и выберите способ доставки</label>
            <div class="checkout-address">
              <input
                id="checkout-address"
                type="text"
                class="checkout-input"
                autocomplete="off"
                v-model="form.addressQuery"
                :placeholder="addressPlaceholder"
                @focus="onAddressInput"
                @input="onAddressInput"
              />
              <button
                v-if="form.selectedAddress && !state.isLoadingSuggestions"
                type="button"
                class="checkout-input__clear"
                @click="clearAddress"
                aria-label="Очистить адрес"
              >
                ×
              </button>
              <span
                v-else-if="state.isLoadingSuggestions"
                class="checkout-input__spinner"
                role="status"
                aria-label="Ищем адреса"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"
                  />
                </svg>
              </span>
              <ul v-if="addressSuggestions.length" class="checkout-suggestions">
                <li
                    v-for="item in addressSuggestions"
                    :key="item.value + item.postalCode"
                    class="checkout-suggestions__item"
                    @mousedown.prevent="selectAddress(item)"
                >
                  <div class="checkout-suggestions__title">{{ item.value }}</div>
                  <div class="checkout-suggestions__meta">
                    <span>{{ item.region }}</span>
                    <span v-if="item.hasTerminals" class="checkout-tag checkout-tag--success">Есть терминалы СДЭК</span>
                    <span v-else class="checkout-tag checkout-tag--warning">Нет терминалов СДЭК</span>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <div class="checkout-block checkout-block--delivery">
            <h2 class="checkout-subtitle">Выберите способ доставки</h2>
            <div class="checkout-delivery">
              <button
                v-for="method in deliveryMethods"
                :key="method.code"
                type="button"
                class="checkout-delivery__item"
                :class="{
                  'checkout-delivery__item--active': form.deliveryType === method.code,
                  'checkout-delivery__item--disabled': isDeliveryMethodDisabled(method)
                }"
                :disabled="isDeliveryMethodDisabled(method)"
                @click="selectDelivery(method.code)"
              >
                <span class="checkout-delivery__icon" aria-hidden="true">
                  <svg
                    v-if="method.code === 'terminal'"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="m7.875 14.25 1.214 1.942a2.25 2.25 0 0 0 1.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 0 1 1.872 1.002l.164.246a2.25 2.25 0 0 0 1.872 1.002h2.092a2.25 2.25 0 0 0 1.872-1.002l.164-.246A2.25 2.25 0 0 1 16.954 9h4.636M2.41 9a2.25 2.25 0 0 0-.16.832V12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 12V9.832c0-.287-.055-.57-.16-.832M2.41 9a2.25 2.25 0 0 1 .382-.632l3.285-3.832a2.25 2.25 0 0 1 1.708-.786h8.43c.657 0 1.281.287 1.709.786l3.284 3.832c.163.19.291.404.382.632M4.5 20.25h15A2.25 2.25 0 0 0 21.75 18v-2.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25Z"
                    />
                  </svg>
                  <svg
                    v-else-if="method.code === 'courier'"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"
                    />
                  </svg>
                  <svg
                    v-else-if="method.code === 'pickup'"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"
                    />
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M3.75 4.5h16.5M3.75 9h16.5M3.75 13.5h16.5M3.75 18h16.5"
                    />
                  </svg>
                </span>
                <span class="checkout-delivery__content">
                  <span class="checkout-delivery__title">{{ method.title }}</span>
                  <span class="checkout-delivery__description">{{ method.description }}</span>
                  <span class="checkout-delivery__price">
                    <template v-if="method.loading">Рассчитываем...</template>
                    <template v-else-if="method.price">{{ method.price }}</template>
                    <template v-else>—</template>
                  </span>
                </span>
              </button>
            </div>
            <transition name="checkout-fade">
              <div v-if="form.deliveryType === 'terminal'">
                <p v-if="state.isLoadingDeliveryCost" class="checkout-hint checkout-hint--muted">
                  Рассчитываем стоимость доставки...
                </p>
                <p v-if="state.deliveryCostError" class="checkout-error">{{ state.deliveryCostError }}</p>
                <div v-if="form.terminal.code" class="checkout-terminal">
                  <div class="checkout-terminal__header">
                    <h3 class="checkout-terminal__title">{{ form.terminal.name }}</h3>
                    <button type="button" class="checkout-terminal__change" @click="openTerminalModal">Выбрать другой пункт</button>
                  </div>
                  <p class="checkout-terminal__line">{{ form.terminal.address }}</p>
                  <p v-if="form.terminal.workTime" class="checkout-terminal__line">График работы: {{ form.terminal.workTime }}</p>
                  <p v-if="form.terminal.phones && form.terminal.phones.length" class="checkout-terminal__line">
                    Телефон: {{ form.terminal.phones.join(', ') }}
                  </p>
                  <p v-if="form.terminal.note" class="checkout-terminal__note">{{ form.terminal.note }}</p>
                </div>
                <div v-if="state.terminalError" class="checkout-error">{{ state.terminalError }}</div>
              </div>
            </transition>
            <transition name="checkout-fade">
              <div v-if="form.deliveryType === 'courier'" class="checkout-delivery-details">
                <div class="checkout-grid">
                  <label class="checkout-field">
                    <span class="checkout-field__label">Город</span>
                    <input type="text" class="checkout-input" v-model="form.courier.city" placeholder="Например: Казань" />
                  </label>
                  <label class="checkout-field">
                    <span class="checkout-field__label">Улица</span>
                    <input type="text" class="checkout-input" v-model="form.courier.street" placeholder="Улица" />
                  </label>
                </div>
                <div class="checkout-grid checkout-grid--small">
                  <label class="checkout-field">
                    <span class="checkout-field__label">Дом / Корпус</span>
                    <input type="text" class="checkout-input" v-model="form.courier.house" placeholder="Дом" />
                  </label>
                  <label class="checkout-field">
                    <span class="checkout-field__label">Квартира</span>
                    <input type="text" class="checkout-input" v-model="form.courier.flat" placeholder="Квартира" />
                  </label>
                </div>
                <p v-if="state.deliveryCostError" class="checkout-error">{{ state.deliveryCostError }}</p>
                <label class="checkout-field">
                  <span class="checkout-field__label">Комментарий для курьера</span>
                  <textarea
                    class="checkout-textarea"
                    rows="2"
                    v-model="form.courier.comment"
                    placeholder="Подробности для курьера"
                  ></textarea>
                </label>
              </div>
            </transition>

            <transition name="checkout-fade">
              <div v-if="form.deliveryType === 'pickup'" class="checkout-delivery-details">
                <div class="checkout-alert checkout-alert--warning">
                  <strong>Важно!</strong> При выборе самовывоза товары нужно будет самостоятельно забрать из филиала.
                </div>
                <div class="checkout-pickup-list">
                  <article v-for="item in pickupHints" :key="item.productId" class="checkout-pickup-item">
                    <header class="checkout-pickup-item__header">
                      <h3 class="checkout-pickup-item__title">{{ item.name }}</h3>
                    </header>
                    <p class="checkout-pickup-item__line">Адрес: {{ item.address }}</p>
                    <p class="checkout-pickup-item__line">График работы: {{ item.schedule }}</p>
                  </article>
                </div>
              </div>
            </transition>
          </div>
        </section>

        <section class="checkout-section">
          <h2 class="checkout-section__title">2. Как вам будет удобнее оплатить заказ?</h2>
          <div class="checkout-payment">
            <label
              v-for="method in paymentMethods"
              :key="method.code"
              class="checkout-payment__item"
              :class="{ 'checkout-payment__item--active': form.paymentType === method.code }"
            >
              <input
                class="checkout-payment__radio"
                type="radio"
                name="checkout-payment"
                :value="method.code"
                v-model="form.paymentType"
              />
              <span class="checkout-payment__title">{{ method.title }}</span>
              <span class="checkout-payment__description">{{ method.description }}</span>
            </label>
          </div>
        </section>

        <section class="checkout-section">
          <h2 class="checkout-section__title">3. Укажите данные получателя заказа</h2>
          <div class="checkout-grid">
            <label :class="['checkout-field', { 'checkout-field--error': isFieldInvalid('phone') }]">
              <span class="checkout-field__label">
                Номер телефона
                <span class="checkout-field__required" aria-hidden="true">*</span>
              </span>
              <input
                type="tel"
                :class="['checkout-input', { 'checkout-input--error': isFieldInvalid('phone') }]"
                v-model="form.recipient.phone"
                placeholder="+7 (___) ___-__-__"
                :disabled="isPhoneLocked"
              />
              <span v-if="isFieldInvalid('phone')" class="checkout-field__error">{{ getFieldError('phone') }}</span>
            </label>
            <div v-if="requiresSmsVerification" class="checkout-sms">
              <template v-if="smsVerification.isVerified">
                <p class="checkout-hint checkout-hint--success">
                  {{ smsVerification.status || 'Телефон подтверждён. Вы можете завершить оформление заказа.' }}
                </p>
              </template>
              <template v-else>
                <p class="checkout-hint checkout-hint--muted">
                  После заполнения обязательных полей нажмите «Оформить заказ», мы отправим SMS с кодом подтверждения.
                </p>
                <p v-if="smsVerification.status" class="checkout-hint checkout-hint--success">
                  {{ smsVerification.status }}
                </p>
                <p v-if="smsVerification.error" class="checkout-error">{{ smsVerification.error }}</p>
              </template>
            </div>
            <label :class="['checkout-field', { 'checkout-field--error': isFieldInvalid('email') }]">
              <span class="checkout-field__label">
                E-mail
                <span class="checkout-field__required" aria-hidden="true">*</span>
              </span>
              <input
                type="email"
                :class="['checkout-input', { 'checkout-input--error': isFieldInvalid('email') }]"
                v-model="form.recipient.email"
                placeholder="email@example.com"
              />
              <span v-if="isFieldInvalid('email')" class="checkout-field__error">{{ getFieldError('email') }}</span>
            </label>
          </div>
          <div class="checkout-grid checkout-grid--three">
            <label :class="['checkout-field', { 'checkout-field--error': isFieldInvalid('firstName') }]">
              <span class="checkout-field__label">
                Имя
                <span class="checkout-field__required" aria-hidden="true">*</span>
              </span>
              <input
                type="text"
                :class="['checkout-input', { 'checkout-input--error': isFieldInvalid('firstName') }]"
                v-model="form.recipient.firstName"
                placeholder="Имя"
              />
              <span v-if="isFieldInvalid('firstName')" class="checkout-field__error">{{ getFieldError('firstName') }}</span>
            </label>
            <label :class="['checkout-field', { 'checkout-field--error': isFieldInvalid('lastName') }]">
              <span class="checkout-field__label">
                Фамилия
                <span class="checkout-field__required" aria-hidden="true">*</span>
              </span>
              <input
                type="text"
                :class="['checkout-input', { 'checkout-input--error': isFieldInvalid('lastName') }]"
                v-model="form.recipient.lastName"
                placeholder="Фамилия"
              />
              <span v-if="isFieldInvalid('lastName')" class="checkout-field__error">{{ getFieldError('lastName') }}</span>
            </label>
            <label :class="['checkout-field', { 'checkout-field--error': isFieldInvalid('middleName') }]">
              <span class="checkout-field__label">
                Отчество
                <span class="checkout-field__required" aria-hidden="true">*</span>
              </span>
              <input
                type="text"
                :class="['checkout-input', { 'checkout-input--error': isFieldInvalid('middleName') }]"
                v-model="form.recipient.middleName"
                placeholder="Отчество"
              />
              <span v-if="isFieldInvalid('middleName')" class="checkout-field__error">{{ getFieldError('middleName') }}</span>
            </label>
          </div>
        </section>
      </main>

      <aside class="checkout-page__sidebar">
        <div class="checkout-summary">
          <h2 class="checkout-summary__title">Состав заказа</h2>
          <div v-if="!basketValidation.valid" class="checkout-alert checkout-alert--error">
            <strong>Внимание!</strong> {{ basketValidation.error }}
            <template v-if="basketValidation.conflicts">
              <p style="margin-top: 8px; font-size: 13px;">
                Оформление заказа невозможно. Пожалуйста, удалите товары из разных городов.
              </p>
            </template>
          </div>
          <ul class="checkout-summary__list">
            <li v-for="item in cart.items" :key="item.basketId" class="checkout-summary__item">
              <div class="checkout-summary__item-name">{{ item.name }}</div>
              <div class="checkout-summary__item-price" v-html="item.sumPrint"></div>
            </li>
          </ul>
          <dl class="checkout-summary__line">
            <dt>Товары, {{ cart.totalQty }} шт.</dt>
            <dd v-html="cart.totalSumPrint"></dd>
          </dl>
          <dl class="checkout-summary__line">
            <dt>Оплата</dt>
            <dd>{{ paymentTitle }}</dd>
          </dl>
          <dl v-if="shouldDisplayDeliveryCost" class="checkout-summary__line">
            <dt>Доставка</dt>
            <dd>{{ summaryDeliveryCostText }}</dd>
          </dl>
          <dl class="checkout-summary__total">
            <dt>Итого</dt>
            <dd v-html="summaryTotalHtml"></dd>
          </dl>
          <div class="checkout-summary__consent">
            <label
              class="checkout-consent"
              :class="{ 'checkout-consent--error': state.showValidationErrors && !form.agreement }"
              :data-error="state.showValidationErrors && !form.agreement ? 'Обязательное поле' : ''"
            >
              <input
                class="checkout-consent__input"
                type="checkbox"
                v-model="form.agreement"
                @change="state.showValidationErrors = false"
              />
              <span class="checkout-consent__box"></span>
              <span class="checkout-consent__text">
                Я подтверждаю свое совершеннолетие и соглашаюсь на
                <a href="/privacy-policy/" target="_blank" rel="noopener">обработку персональных данных</a>
                в соответствии с
                <a href="/polzovatelskoe-soglashenie/" target="_blank" rel="noopener">Пользовательским соглашением</a>
                и принимаю
                <a href="/dogovor-oferty/" target="_blank" rel="noopener">договор оферты</a>.
              </span>
            </label>
          </div>
          <button
            type="button"
            class="checkout-button checkout-button--primary"
            @click="submitOrder"
          >
            {{ state.isSubmitting ? 'Отправляем...' : 'Оформить заказ' }}
          </button>
          <p v-if="state.submitError" class="checkout-error checkout-error--inline">{{ state.submitError }}</p>
        </div>
      </aside>
    </div>

    <div v-else class="checkout-empty">
      <h1 class="checkout-empty__title">Ваша корзина пуста</h1>
      <p class="checkout-empty__text">Добавьте товары в корзину, чтобы оформить заказ.</p>
      <a class="checkout-button checkout-button--primary" href="/catalog/">Перейти в каталог</a>
    </div>

    <transition name="checkout-fade">
      <div v-if="state.showTerminalModal" class="checkout-modal" role="dialog" aria-modal="true">
        <div class="checkout-modal__backdrop" @click="closeTerminalModal"></div>
        <div class="checkout-modal__dialog">
          <header class="checkout-modal__header">
            <h2 class="checkout-modal__title">Выберите удобный для вас терминал</h2>
            <button type="button" class="checkout-modal__close" @click="closeTerminalModal" aria-label="Закрыть">×</button>
          </header>
          <div class="checkout-modal__body">
            <div class="checkout-modal__controls">
              <input
                type="text"
                class="checkout-input checkout-modal__search"
                placeholder="Поиск по адресу"
                v-model="state.terminalSearch"
              />
              <div class="checkout-modal__view">
                <button
                  type="button"
                  :class="['checkout-switch', { 'checkout-switch--active': state.terminalView === 'map' }]"
                  @click="setTerminalView('map')"
                >
                  Карта
                </button>
                <button
                  type="button"
                  :class="['checkout-switch', { 'checkout-switch--active': state.terminalView === 'list' }]"
                  @click="setTerminalView('list')"
                >
                  Список
                </button>
              </div>
            </div>
            <div class="checkout-modal__content" :class="{ 'checkout-modal__content--list-only': state.terminalView === 'list' }">
              <div v-if="state.terminalView === 'map'" class="checkout-modal__map">
                <div
                    ref="terminalMap"
                    class="checkout-modal__map-inner"
                    style="width: 100%; height: 400px"
                ></div>

                <div v-if="state.isLoadingTerminals" class="checkout-modal__loader">
                  Загружаем карту...
                </div>

                <div v-if="state.terminalError" class="checkout-modal__error">
                  {{ state.terminalError }}
                </div>
              </div>
              <div class="checkout-modal__list">
                <div v-if="state.isLoadingTerminals" class="checkout-modal__loader">Загружаем список терминалов...</div>
                <template v-else-if="filteredTerminals.length">
                  <div
                      v-for="point in filteredTerminals"
                      :key="point.code"
                      class="checkout-terminal-card"
                  >
                    <div class="checkout-terminal-card__content">
                      <h3 class="checkout-terminal-card__title">{{ point.name }}</h3>
                      <p class="checkout-terminal-card__line">{{ point.address }}</p>
                      <p v-if="point.workTime" class="checkout-terminal-card__note">
                        {{ point.workTime }}
                      </p>
                      <p v-if="point.phones && point.phones.length" class="checkout-terminal-card__note">
                        {{ point.phones.join(', ') }}
                      </p>
                    </div>

                    <button
                        class="checkout-terminal-card__button"
                        @click="selectTerminal(point)"
                    >
                      Выбрать
                    </button>
                  </div>
                </template>
                <div v-else class="checkout-modal__empty">
                  Терминалы не найдены
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
    <transition name="checkout-fade">
      <div v-if="state.showSmsModal" class="checkout-modal checkout-modal--sms" role="dialog" aria-modal="true">
        <div class="checkout-modal__backdrop" @click="closeSmsModal"></div>
        <div class="checkout-modal__dialog checkout-modal__dialog--narrow">
          <header class="checkout-modal__header">
            <h2 class="checkout-modal__title">Подтверждение номера телефона</h2>
            <button type="button" class="checkout-modal__close" @click="closeSmsModal" aria-label="Закрыть">×</button>
          </header>
          <div class="checkout-modal__body checkout-sms-modal">
            <p class="checkout-sms-modal__description">
              Мы отправили Вам код подтверждения на телефон {{ getSmsDisplayPhone() }}
            </p>
            <label class="checkout-field checkout-sms-modal__field">
              <span class="checkout-field__label">Код из SMS</span>
              <input
                type="text"
                class="checkout-input"
                v-model="smsVerification.code"
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]*"
                placeholder="123456"
                ref="smsCodeInput"
              />
            </label>
            <p v-if="smsVerification.status" class="checkout-hint checkout-hint--success checkout-sms-modal__status">
              {{ smsVerification.status }}
            </p>
            <p v-if="smsVerification.error" class="checkout-error checkout-sms-modal__error">{{ smsVerification.error }}</p>
            <div class="checkout-sms-modal__actions">
              <button
                type="button"
                class="checkout-button checkout-button--primary"
                :disabled="smsVerification.isChecking || !canConfirmSms"
                @click="handleSmsModalConfirm"
              >
                {{ smsVerification.isChecking ? 'Проверяем...' : 'Продолжить' }}
              </button>
              <button type="button" class="checkout-button checkout-button--outline" @click="closeSmsModal">Отменить</button>
            </div>
            <div class="checkout-sms-modal__resend">
              <button
                v-if="smsVerification.resendSeconds <= 0"
                type="button"
                class="checkout-link-button"
                :disabled="smsVerification.isSending"
                @click="handleSmsResend"
              >
                {{
                  smsVerification.isSending
                    ? 'Отправляем...'
                    : smsVerification.isCodeSent
                      ? 'Отправить код повторно'
                      : 'Отправить код ещё раз'
                }}
              </button>
              <p v-else class="checkout-sms-modal__hint">
                Отправить ещё раз через {{ smsVerification.resendSeconds }} секунд
              </p>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'DevobOrder',
  data() {
    return {
      cart: { items: [], totalQty: 0, totalSum: 0, totalSumPrint: '300 ₽' },
      user: { isAuthorized: false, phone: '', email: '', firstName: '', lastName: '', middleName: '' },
      deliveryMethods: [],
      originalDeliveryMethods: [],
      paymentMethods: [],
      pickupHints: [],
      settings: {},
      addressSuggestions: [],
      suggestionTimer: null,
      terminals: [],
      deliveryCostRequestId: 0,
      map: { instance: null, collection: null, loader: null, objectManager: null, terminalLookup: {}, apiKey: '', api: null },
      basketValidation: {
        valid: true,
        error: '',
        conflicts: null,
        cityCode: null,
        cityName: '',
      },
      form: {
        addressQuery: '',
        selectedAddress: null,
        deliveryType: '',
        terminal: {},
        courier: { city: '', street: '', house: '', flat: '', comment: '' },
        paymentType: '',
        recipient: { phone: '', email: '', firstName: '', lastName: '', middleName: '' },
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
        deliveryCost: { value: null, text: '', isCalculated: false },
        showTerminalModal: false,
        terminalView: 'list',
        terminalSearch: '',
        showValidationErrors: false,
        showSmsModal: false
      }
    };
  },
  computed: {
    hasItems() {
      return Array.isArray(this.cart.items) && this.cart.items.length > 0;
    },
    isOrderCompleted() {
      return Boolean(this.state.orderId || this.state.orderAccountNumber || this.state.submitSuccess);
    },
    addressPlaceholder() {
      return 'Например: Казань, Татарстан';
    },
    isAddressSelected() {
      return Boolean(this.form.selectedAddress);
    },
    isTerminalAvailable() {
      return Boolean(this.form.selectedAddress && this.form.selectedAddress.hasTerminals !== false);
    },
    requiresSmsVerification() {
      return !(this.user && this.user.isAuthorized);
    },
    isPhoneLocked() {
      return Boolean(this.user && this.user.isAuthorized);
    },
    canRequestSms() {
      if (!this.requiresSmsVerification) {
        return false;
      }

      const requiredFields = ['phone', 'email', 'firstName', 'lastName', 'middleName'];
      return requiredFields.every((field) => !this.getFieldValidation(field).invalid);
    },
    canConfirmSms() {
      const code = (this.smsVerification.code || '').replace(/\D/g, '');
      return code.length >= 4;
    },
    paymentTitle() {
      const current = this.paymentMethods.find((method) => method.code === this.form.paymentType);
      return current ? current.title : 'Не выбран';
    },
    shouldDisplayDeliveryCost() {
      const deliveryState = this.state.deliveryCost || {};
      const deliveryType = this.form.deliveryType;
      const supportsCalculation = deliveryType === 'terminal' || deliveryType === 'courier';
      return (
        supportsCalculation &&
        deliveryState.isCalculated &&
        !this.state.deliveryCostError &&
        (deliveryState.text || typeof deliveryState.value === 'number')
      );
    },
    summaryDeliveryCostText() {
      if (!this.shouldDisplayDeliveryCost) {
        return '';
      }

      const deliveryState = this.state.deliveryCost || {};
      if (deliveryState.text) {
        return deliveryState.text;
      }

      if (typeof deliveryState.value === 'number' && Number.isFinite(deliveryState.value)) {
        return this.formatCurrency(deliveryState.value);
      }

      return '';
    },
    summaryTotalHtml() {
      const baseSum = Number(this.cart.totalSum) || 0;

      if (!this.shouldDisplayDeliveryCost) {
        return this.cart.totalSumPrint || this.formatCurrencyHtml(baseSum);
      }

      const deliveryState = this.state.deliveryCost || {};
      const deliveryValue =
        typeof deliveryState.value === 'number' && Number.isFinite(deliveryState.value)
          ? deliveryState.value
          : 0;

      return this.formatCurrencyHtml(baseSum + deliveryValue);
    },
    orderNumber() {
      const number = this.state.orderAccountNumber || (this.state.orderId ? String(this.state.orderId) : '');
      return number ? `№ ${number}` : '—';
    },
    orderDateFormatted() {
      if (!this.state.orderDate) {
        return '';
      }

      const date = new Date(this.state.orderDate);
      if (Number.isNaN(date.getTime())) {
        return '';
      }

      return date.toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      });
    },
    isFormValid() {
      if (!this.form.agreement) {
        return false;
      }

      if (!this.form.deliveryType || !this.form.paymentType) {
        return false;
      }

      const requiredFields = ['phone', 'email', 'firstName', 'lastName', 'middleName'];
      if (requiredFields.some((field) => this.getFieldValidation(field).invalid)) {
        return false;
      }

      if (this.form.deliveryType === 'terminal') {
        return Boolean(this.form.terminal && this.form.terminal.code);
      }

      if (this.form.deliveryType === 'courier') {
        const courier = this.form.courier || {};
        return Boolean(courier.city && courier.street && courier.house);
      }

      if (!this.form.deliveryType || this.form.deliveryType === 'pickup') {
        return true;
      }

      return Boolean(this.form.selectedAddress);
    },
    isSubmitDisabled() {
      return this.state.isSubmitting || !this.isFormValid;
    },
    filteredTerminals() {
      const query = (this.state.terminalSearch || '').toLowerCase();
      if (!query) {
        return this.terminals;
      }
      return this.terminals.filter((point) => {
        const searchPool = [
          point.name,
          point.address,
          point.workTime,
          Array.isArray(point.phones) ? point.phones.join(' ') : ''
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase();
        return searchPool.indexOf(query) !== -1;
      });
    }
  },
  mounted() {
    this.syncServerData();
    this.initializeFromServer();
    this.checkBasketCities();

    // Применяем маску телефона
    this.$nextTick(() => {
      const phoneInput = this.$el.querySelector('input[type="tel"]');
      if (phoneInput && window.PhoneMask) {
        window.PhoneMask.applyMask(phoneInput);
      }
    });
  },
  watch: {
    'form.recipient.phone'(newValue) {
      if (!this.requiresSmsVerification) {
        return;
      }

      const normalized = this.normalizePhone(newValue);

      if (normalized !== this.smsVerification.phone) {
        this.resetSmsVerificationState();
        this.smsVerification.phone = normalized;
      }
    },
    'user.isAuthorized'(newValue) {
      if (newValue) {
        this.smsVerification.isVerified = true;
        this.smsVerification.status = '';
        this.smsVerification.error = '';
        this.clearSmsTimer();
      }
    }
  },
  beforeUnmount() {
    // Удаляем маску при удалении компонента
    const phoneInput = this.$el.querySelector('input[type="tel"]');
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
    getFieldValidation(field) {
      const recipient = this.form.recipient || {};
      const getValue = (name) => String(recipient[name] || '').trim();

      switch (field) {
        case 'phone': {
          const normalized = this.getNormalizedPhone();
          if (!normalized) {
            return { invalid: true, message: 'Укажите номер телефона' };
          }
          if (normalized.length < 11) {
            return { invalid: true, message: 'Введите корректный номер телефона' };
          }
          return { invalid: false, message: '' };
        }
        case 'email': {
          const email = getValue('email');
          if (!email) {
            return { invalid: true, message: 'Укажите e-mail получателя' };
          }
          if (!this.isValidEmail(email)) {
            return { invalid: true, message: 'Укажите корректный e-mail получателя' };
          }
          return { invalid: false, message: '' };
        }
        case 'firstName':
          if (!getValue('firstName')) {
            return { invalid: true, message: 'Укажите имя получателя' };
          }
          return { invalid: false, message: '' };
        case 'lastName':
          if (!getValue('lastName')) {
            return { invalid: true, message: 'Укажите фамилию получателя' };
          }
          return { invalid: false, message: '' };
        case 'middleName':
          if (!getValue('middleName')) {
            return { invalid: true, message: 'Укажите отчество получателя' };
          }
          return { invalid: false, message: '' };
        default:
          return { invalid: false, message: '' };
      }
    },
    isFieldInvalid(field) {
      if (!this.state.showValidationErrors) {
        return false;
      }

      return this.getFieldValidation(field).invalid;
    },
    getFieldError(field) {
      const result = this.getFieldValidation(field);
      return result.invalid ? result.message : '';
    },
    getSmsDisplayPhone() {
      const recipient = this.form.recipient || {};
      if (recipient.phone) {
        return recipient.phone;
      }

      const source = this.smsVerification.phone || '';
      if (!source) {
        return '';
      }

      const digits = String(source).replace(/\D/g, '');
      if (window.DevobPhoneMask && typeof window.DevobPhoneMask.format === 'function') {
        return window.DevobPhoneMask.format(digits);
      }
      if (window.PhoneMask && typeof window.PhoneMask.format === 'function') {
        return window.PhoneMask.format(digits);
      }
      if (digits.length === 11) {
        return `+${digits[0]} (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9, 11)}`;
      }
      return digits ? `+${digits}` : '';
    },
    normalizePhone(value) {
      const raw = String(value || '').replace(/\D/g, '');
      if (!raw) {
        return '';
      }

      let normalized = raw;
      if (normalized.charAt(0) === '8') {
        normalized = `7${normalized.slice(1)}`;
      }

      if (normalized.charAt(0) !== '7') {
        normalized = `7${normalized}`;
      }

      return normalized.length > 11 ? normalized.slice(0, 11) : normalized;
    },
    getNormalizedPhone() {
      if (this.user && this.user.isAuthorized && this.user.phone) {
        return this.normalizePhone(this.user.phone);
      }

      const phoneValue = this.form.recipient ? this.form.recipient.phone : '';
      if (window.PhoneMask && typeof window.PhoneMask.unmask === 'function') {
        return this.normalizePhone(window.PhoneMask.unmask(phoneValue));
      }

      return this.normalizePhone(phoneValue);
    },
    resetSmsVerificationState() {
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
    clearSmsTimer() {
      if (this.smsVerification.timerId) {
        clearInterval(this.smsVerification.timerId);
        this.smsVerification.timerId = null;
      }
    },
    startSmsTimer(seconds) {
      this.clearSmsTimer();
      let remaining = Number(seconds) || 0;
      this.smsVerification.resendSeconds = remaining;

      if (remaining <= 0) {
        return;
      }

      this.smsVerification.timerId = setInterval(() => {
        remaining -= 1;
        if (remaining <= 0) {
          this.smsVerification.resendSeconds = 0;
          this.clearSmsTimer();
        } else {
          this.smsVerification.resendSeconds = remaining;
        }
      }, 1000);
    },
    isValidEmail(value) {
      const email = String(value || '').trim();
      if (!email) {
        return false;
      }

      const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return pattern.test(email);
    },
    async requestSmsCode() {
      if (!this.requiresSmsVerification || this.smsVerification.isSending) {
        return false;
      }

      const requiredFields = ['phone', 'email', 'firstName', 'lastName', 'middleName'];
      const invalidField = requiredFields.find((field) => this.getFieldValidation(field).invalid);
      if (invalidField) {
        this.smsVerification.error = this.getFieldValidation(invalidField).message;
        return false;
      }

      const phone = this.getNormalizedPhone();
      if (!phone || phone.length < 11) {
        this.smsVerification.error = 'Введите корректный номер телефона';
        return false;
      }

      if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
        console.error('BX.ajax.runComponentAction не найден');
        this.smsVerification.error = 'Сервис подтверждения временно недоступен';
        return false;
      }

      const recipient = this.form.recipient || {};
      this.smsVerification.isSending = true;
      this.smsVerification.error = '';
      this.smsVerification.status = '';
      this.smsVerification.code = '';

      let success = false;

      try {
        const response = await BX.ajax.runComponentAction('devob:order', 'sendSmsCode', {
          mode: 'class',
          data: {
            phone,
            recipient: {
              firstName: recipient.firstName,
              lastName: recipient.lastName,
              middleName: recipient.middleName,
              email: recipient.email
            }
          }
        });

        const data = response && response.data ? response.data : {};

        if (!data.success) {
          this.smsVerification.error = data.error || 'Не удалось отправить SMS. Попробуйте позже';
          if (typeof data.retryAfter === 'number') {
            this.startSmsTimer(data.retryAfter);
          }
        } else {
          this.smsVerification.isCodeSent = true;
          this.smsVerification.status = data.message || 'Код отправлен на указанный номер';
          this.smsVerification.phone = phone;
          const retryAfter = typeof data.retryAfter === 'number' ? data.retryAfter : 0;
          this.startSmsTimer(retryAfter);
          success = true;
        }
      } catch (error) {
        console.error('Ошибка отправки SMS кода', error);
        this.smsVerification.error = 'Не удалось отправить SMS. Попробуйте позже';
      } finally {
        this.smsVerification.isSending = false;
      }

      return success;
    },
    async confirmSmsCode() {
      if (!this.requiresSmsVerification || this.smsVerification.isChecking || !this.canConfirmSms) {
        return false;
      }

      const phone = this.smsVerification.phone || this.getNormalizedPhone();
      const code = (this.smsVerification.code || '').replace(/\D/g, '');

      if (!phone || phone.length < 11) {
        this.smsVerification.error = 'Введите корректный номер телефона';
        return false;
      }

      if (!code) {
        this.smsVerification.error = 'Введите код из SMS';
        return false;
      }

      if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
        console.error('BX.ajax.runComponentAction не найден');
        this.smsVerification.error = 'Сервис подтверждения временно недоступен';
        return false;
      }

      this.smsVerification.isChecking = true;
      this.smsVerification.error = '';
      this.smsVerification.status = '';

      let success = false;

      try {
        const payload = {
          phone,
          code,
          recipient: {
            firstName: this.form.recipient.firstName,
            lastName: this.form.recipient.lastName,
            middleName: this.form.recipient.middleName,
            email: this.form.recipient.email
          }
        };

        const response = await BX.ajax.runComponentAction('devob:order', 'verifySmsCode', {
          mode: 'class',
          data: payload
        });

        const data = response && response.data ? response.data : {};

        if (!data.success) {
          this.smsVerification.error = data.error || 'Не удалось подтвердить код';
          if (typeof data.attemptsLeft === 'number') {
            this.smsVerification.attemptsLeft = data.attemptsLeft;
            if (data.attemptsLeft >= 0) {
              this.smsVerification.status = `Осталось попыток: ${data.attemptsLeft}`;
            }
          }
        } else {
          this.smsVerification.isVerified = true;
          this.smsVerification.status = data.message || 'Телефон успешно подтверждён';
          this.smsVerification.error = '';
          this.smsVerification.attemptsLeft = null;
          this.clearSmsTimer();
          if (data.phone) {
            this.smsVerification.phone = data.phone;
          }
          if (data.authorized) {
            this.user.isAuthorized = true;
          }
          success = true;
        }
      } catch (error) {
        console.error('Ошибка подтверждения SMS кода', error);
        this.smsVerification.error = 'Не удалось подтвердить код. Попробуйте позже';
      } finally {
        this.smsVerification.isChecking = false;
      }

      return success;
    },
    openSmsModal() {
      this.state.showSmsModal = true;
      this.$nextTick(() => {
        const input = this.$refs.smsCodeInput;
        if (input && typeof input.focus === 'function') {
          input.focus();
        }
      });
    },
    closeSmsModal() {
      this.state.showSmsModal = false;
    },
    async handleSmsModalConfirm() {
      if (this.smsVerification.isChecking) {
        return;
      }

      const success = await this.confirmSmsCode();
      if (success) {
        this.state.showSmsModal = false;
        this.state.submitError = '';
        this.submitOrder();
      }
    },
    async handleSmsResend() {
      if (this.smsVerification.isSending || this.smsVerification.resendSeconds > 0) {
        return;
      }

      await this.requestSmsCode();
      this.$nextTick(() => {
        const input = this.$refs.smsCodeInput;
        if (input && typeof input.focus === 'function') {
          input.focus();
        }
      });
    },
    formatCurrency(amount) {
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
    isDeliveryMethodDisabled(method) {
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
    getFallbackDeliveryMethodCode() {
      if (!Array.isArray(this.deliveryMethods) || !this.deliveryMethods.length) {
        return '';
      }

      const preferredCodes = ['courier', 'pickup'];
      for (const code of preferredCodes) {
        if (this.deliveryMethods.some((method) => method.code === code)) {
          return code;
        }
      }

      const fallback = this.deliveryMethods.find((method) => method.code !== 'terminal');
      return fallback ? fallback.code : '';
    },
    handleTerminalNotAvailableForAddress(options = {}) {
      const { updateCourierCost = true } = options;
      this.state.deliveryCostError = '';
      this.state.terminalError = 'Терминалы СДЭК для выбранного города не найдены';
      this.state.showTerminalModal = false;
      this.form.terminal = {};
      this.terminals = [];
      this.state.isLoadingTerminals = false;

      const fallbackCode = this.getFallbackDeliveryMethodCode();
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
    formatCurrencyHtml(amount) {
      const formatted = this.formatCurrency(amount);
      return formatted ? formatted.replace(/\u00A0/g, '&nbsp;') : '';
    },
    setDeliveryCost(value, text, isCalculated = false) {
      const numericValue = typeof value === 'number' && Number.isFinite(value) ? value : null;
      this.state.deliveryCost = {
        value: numericValue,
        text: text || '',
        isCalculated: Boolean(isCalculated)
      };
    },
    resetDeliveryCost() {
      this.setDeliveryCost(null, '', false);
    },
    /**
     * Проверяет, что все товары в корзине из одного города
     */
    async checkBasketCities() {
      if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
        console.error('BX.ajax.runComponentAction не найден');
        return false;
      }

      try {
        const response = await BX.ajax.runComponentAction('devob:order', 'getBasketCitiesInfo', {
          mode: 'class',
          data: {}
        });

        const data = response && response.data ? response.data : {};

        if (!data.valid) {
          this.basketValidation.valid = false;
          this.basketValidation.error = data.error || 'В корзине товары из разных городов';
          this.basketValidation.conflicts = data.conflicts || null;
          return false;
        }

        this.basketValidation.valid = true;
        this.basketValidation.error = '';
        this.basketValidation.conflicts = null;
        this.basketValidation.cityCode = data.cityCode || null;
        this.basketValidation.cityName = data.cityName || '';
        return true;

      } catch (error) {
        console.error('Ошибка проверки городов корзины:', error);
        this.basketValidation.valid = false;
        this.basketValidation.error = 'Не удалось проверить корзину';
        return false;
      }
    },
    syncServerData() {
      if (Array.isArray(this.delivery_methods) && this.delivery_methods.length) {
        const normalized = this.delivery_methods.map((method) => ({
          ...method,
          loading: false
        }));

        if (!this.originalDeliveryMethods.length) {
          this.originalDeliveryMethods = normalized.map((method) => ({ ...method }));
        }

        if (!this.deliveryMethods.length) {
          this.deliveryMethods = normalized.map((method) => ({ ...method }));
        }
      }

      if (Array.isArray(this.payment_methods) && !this.paymentMethods.length) {
        this.paymentMethods = this.payment_methods.slice();
      }

      if (Array.isArray(this.pickup_hints) && !this.pickupHints.length) {
        this.pickupHints = this.pickup_hints.slice();
      }
    },
    updateDeliveryMethod(code, patch = {}) {
      if (!Array.isArray(this.deliveryMethods) || !this.deliveryMethods.length) {
        return;
      }
      const index = this.deliveryMethods.findIndex((method) => method.code === code);
      if (index === -1) {
        return;
      }
      const updated = Object.assign({}, this.deliveryMethods[index], patch);
      this.deliveryMethods.splice(index, 1, updated);
    },
    getOriginalDeliveryMethod(code) {
      if (!Array.isArray(this.originalDeliveryMethods)) {
        return undefined;
      }
      return this.originalDeliveryMethods.find((method) => method.code === code);
    },
    resetTerminalDeliveryMethod() {
      this.resetDeliveryCost();
      const original = this.getOriginalDeliveryMethod('terminal');
      if (original) {
        this.updateDeliveryMethod('terminal', {
          price: original.price || '',
          loading: false
        });
      } else {
        this.updateDeliveryMethod('terminal', { price: '', loading: false });
      }
    },
    resetCourierDeliveryMethod() {
      const original = this.getOriginalDeliveryMethod('courier');
      if (original) {
        this.updateDeliveryMethod('courier', {
          price: original.price || '',
          loading: false
        });
      } else {
        this.updateDeliveryMethod('courier', { price: '', loading: false });
      }
    },
    clearTerminalDeliveryCostState(options = {}) {
      const { resetPrice = true } = options;
      this.deliveryCostRequestId += 1;
      this.state.isLoadingDeliveryCost = false;
      this.state.deliveryCostError = '';
      this.resetDeliveryCost();
      if (resetPrice) {
        this.resetTerminalDeliveryMethod();
      } else {
        this.updateDeliveryMethod('terminal', { loading: false });
      }
    },
    clearCourierDeliveryCostState(options = {}) {
      const { resetPrice = true } = options;
      this.deliveryCostRequestId += 1;
      this.state.isLoadingDeliveryCost = false;
      this.state.deliveryCostError = '';
      this.resetDeliveryCost();
      if (resetPrice) {
        this.resetCourierDeliveryMethod();
      } else {
        this.updateDeliveryMethod('courier', { loading: false });
      }
    },
    updateTerminalDeliveryCost(options = {}) {
      const { force = false } = options;

      if (!force && this.form.deliveryType !== 'terminal') {
        return;
      }

      const address = this.form.selectedAddress;
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

      const requestId = ++this.deliveryCostRequestId;
      this.state.isLoadingDeliveryCost = true;
      this.state.deliveryCostError = '';
      this.resetDeliveryCost();
      this.updateDeliveryMethod('terminal', { loading: true, price: '' });
      const finalizeRequest = () => {
        if (requestId === this.deliveryCostRequestId) {
          this.state.isLoadingDeliveryCost = false;
          this.updateDeliveryMethod('terminal', { loading: false });
        }
      };

      BX.ajax
        .runComponentAction('devob:order', 'calculateTerminalDelivery', {
          mode: 'class',
          data: { address }
        })
        .then((response) => {
          if (requestId !== this.deliveryCostRequestId) {
            return;
          }

          const data = response && response.data ? response.data : {};
          if (!data.success) {
            const errorMessage = data.error || 'Не удалось рассчитать стоимость доставки';
            this.state.deliveryCostError = errorMessage;
            this.resetTerminalDeliveryMethod();
            finalizeRequest();
            return;
          }

          let priceText = '';
          if (data.pricePrint) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = data.pricePrint;
            priceText = textarea.value;
          } else if (typeof data.price !== 'undefined' && data.price !== null) {
            priceText = String(data.price) + ' ₽';
          }

          let rawPrice = null;
          if (typeof data.price === 'number') {
            rawPrice = data.price;
          } else if (typeof data.price === 'string' && data.price.trim() !== '') {
            rawPrice = Number.parseFloat(data.price.replace(',', '.'));
          }

          const priceValue = typeof rawPrice === 'number' && Number.isFinite(rawPrice) ? rawPrice : null;
          const finalPriceText = priceText || (priceValue !== null ? this.formatCurrency(priceValue) : '');

          this.updateDeliveryMethod('terminal', {
            loading: false,
            price: finalPriceText
          });
          this.setDeliveryCost(priceValue, finalPriceText, Boolean(finalPriceText || priceValue !== null));
          this.state.deliveryCostError = '';
          finalizeRequest();
        })
        .catch((error) => {
          if (requestId !== this.deliveryCostRequestId) {
            return;
          }
          console.error('Ошибка расчёта стоимости терминальной доставки', error);
          this.state.deliveryCostError = 'Не удалось рассчитать стоимость доставки';
          this.resetTerminalDeliveryMethod();
          finalizeRequest();
        });
    },
    updateCourierDeliveryCost() {
      if (this.form.deliveryType !== 'courier') {
        return;
      }

      const address = this.form.selectedAddress;
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

      const requestId = ++this.deliveryCostRequestId;
      this.state.isLoadingDeliveryCost = true;
      this.state.deliveryCostError = '';
      this.resetDeliveryCost();
      this.updateDeliveryMethod('courier', { loading: true, price: '' });
      const finalizeRequest = () => {
        if (requestId === this.deliveryCostRequestId) {
          this.state.isLoadingDeliveryCost = false;
          this.updateDeliveryMethod('courier', { loading: false });
        }
      };

      BX.ajax
          .runComponentAction('devob:order', 'calculateCourierDelivery', {
            mode: 'class',
            data: { address }
          })
          .then((response) => {
            if (requestId !== this.deliveryCostRequestId) {
              return;
            }

            const data = response && response.data ? response.data : {};
            if (!data.success) {
              const errorMessage = data.error || 'Не удалось рассчитать стоимость доставки';
              this.state.deliveryCostError = errorMessage;
              this.resetCourierDeliveryMethod();
              finalizeRequest();
              return;
            }

            let priceText = '';
            if (data.pricePrint) {
              const textarea = document.createElement('textarea');
              textarea.innerHTML = data.pricePrint;
              priceText = textarea.value;
            } else if (typeof data.price !== 'undefined' && data.price !== null) {
              priceText = String(data.price) + ' ₽';
            }

            let rawPrice = null;
            if (typeof data.price === 'number') {
              rawPrice = data.price;
            } else if (typeof data.price === 'string' && data.price.trim() !== '') {
              rawPrice = Number.parseFloat(data.price.replace(',', '.'));
            }

            const priceValue = typeof rawPrice === 'number' && Number.isFinite(rawPrice) ? rawPrice : null;
            const finalPriceText = priceText || (priceValue !== null ? this.formatCurrency(priceValue) : '');

            this.updateDeliveryMethod('courier', {
              loading: false,
              price: finalPriceText
            });
            this.setDeliveryCost(priceValue, finalPriceText, Boolean(finalPriceText || priceValue !== null));
            this.state.deliveryCostError = '';
            finalizeRequest();
          })
          .catch((error) => {
            if (requestId !== this.deliveryCostRequestId) {
              return;
            }
            console.error('Ошибка расчёта стоимости курьерской доставки', error);
            this.state.deliveryCostError = 'Не удалось рассчитать стоимость доставки';
            this.resetCourierDeliveryMethod();
            finalizeRequest();
          });
    },
    initializeFromServer() {
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
          this.handleTerminalNotAvailableForAddress({ updateCourierCost: false });
        } else {
          this.$nextTick(() => {
            this.updateTerminalDeliveryCost();
          });
        }
      }
      if (this.form.deliveryType === 'courier' && this.form.selectedAddress) {
        this.$nextTick(() => {
          this.updateCourierDeliveryCost();
        });
      }

      this.resetSmsVerificationState();
      this.smsVerification.phone = this.getNormalizedPhone();
    },
    onAddressInput(event = null) {
      const query = (this.form.addressQuery || '').trim();

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
      this.suggestionTimer = setTimeout(() => {
        this.fetchAddressSuggestions(query);
      }, 200);
    },
    fetchAddressSuggestions(query) {
      if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
        console.error('BX.ajax.runComponentAction не найден');
        this.state.isLoadingSuggestions = false;
        return;
      }

      const finalizeSuggestions = () => {
        this.state.isLoadingSuggestions = false;
      };

      const limit = 10;

      BX.ajax
        .runComponentAction('devob:order', 'suggestAddress', {
          mode: 'class',
          data: { query, limit }
        })
        .then((response) => {
          const data = response && response.data ? response.data : {};
          if (!data.success) {
            this.addressSuggestions = [];
            if (data.error) {
              console.warn(data.error);
            }
            finalizeSuggestions();
            return;
          }
          this.addressSuggestions = Array.isArray(data.items) ? data.items : [];
          finalizeSuggestions();
        })
        .catch((error) => {
          console.error('Ошибка загрузки подсказок адреса', error);
          finalizeSuggestions();
        });
    },
    selectAddress(item) {
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
    clearAddress() {
      if (this.suggestionTimer) {
        clearTimeout(this.suggestionTimer);
        this.suggestionTimer = null;
      }
      this.form.addressQuery = '';
      this.form.selectedAddress = null;
      this.addressSuggestions = [];
      this.form.terminal = {};
      this.terminals = [];
      this.form.courier = { city: '', street: '', house: '', flat: '', comment: '' };
      this.form.deliveryType = '';
      this.state.isLoadingSuggestions = false;
      this.state.terminalError = '';
      this.clearTerminalDeliveryCostState();
      this.clearCourierDeliveryCostState();
      this.state.showTerminalModal = false;
    },
    selectDelivery(code) {
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
    openTerminalModal() {
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
      this.loadTerminals().then(() => {
        this.$nextTick(() => {
          // this.ensureMapReady();
        });
      });
    },
    closeTerminalModal() {
      this.state.showTerminalModal = false;
    },
    loadTerminals() {
      return new Promise((resolve, reject) => {
        if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
          console.error('BX.ajax.runComponentAction не найден');
          reject(new Error('BX не найден'));
          return;
        }

        this.state.isLoadingTerminals = true;
        this.state.terminalError = '';

        BX.ajax
            .runComponentAction('devob:order', 'loadTerminals', {
              mode: 'class',
              data: { address: this.form.selectedAddress }
            })
            .then((response) => {
              const data = response && response.data ? response.data : {};

              if (!data.success) {
                this.terminals = [];
                this.state.terminalError = data.error || 'Не удалось получить список терминалов';
                this.state.isLoadingTerminals = false;
                reject(new Error(this.state.terminalError));
                return;
              }

              this.terminals = Array.isArray(data.items) ? data.items : [];
              if (data.yandexApiKey) {
                this.map.apiKey = data.yandexApiKey;
              }
              console.log('Загруженные терминалы:', this.terminals);

              if (!this.terminals.length) {
                this.state.terminalError = 'Терминалы СДЭК для выбранного города не найдены';
                this.state.isLoadingTerminals = false;
                reject(new Error(this.state.terminalError));
                return;
              }

              this.state.isLoadingTerminals = false;
              resolve(this.terminals);  // ✓ Вернули терминалы!
            })
            .catch((error) => {
              console.error('Ошибка загрузки терминалов', error);
              this.state.terminalError = 'Не удалось получить список терминалов';
              this.state.isLoadingTerminals = false;
              reject(error);
            });
      });
    },
    selectTerminal(point, closeModal = true) {
      this.form.terminal = Object.assign({}, point);
      if (closeModal) {
        this.closeTerminalModal();
      }
    },
    setTerminalView(view) {
      this.state.terminalView = view;

      if (view === 'map') {
        this.$nextTick(() => {
          // Проверяем, что терминалы уже загружены
          if (this.terminals && this.terminals.length > 0) {
            // this.ensureMapReady();
          } else {
            console.warn('Переключились на карту, но терминалы ещё не загружены');
          }
        });
      }
    },
    async submitOrder() {
      const isBasketValid = await this.checkBasketCities();
      if (!isBasketValid) {
        this.state.submitError = this.basketValidation.error || 'В корзине товары из разных городов';
        this.state.submitSuccess = '';
        this.state.orderId = null;
        this.state.orderAccountNumber = '';
        return;
      }

      if (!window.BX || !BX.ajax || !BX.ajax.runComponentAction) {
        console.error('BX.ajax.runComponentAction не найден');
        this.state.orderId = null;
        this.state.orderAccountNumber = '';
        return;
      }

      if (!this.isFormValid) {
        this.state.showValidationErrors = true;
        this.state.submitError = 'Пожалуйста, заполните обязательные поля и подтвердите согласие на обработку персональных данных';
        this.state.submitSuccess = '';
        this.state.orderId = null;
        this.state.orderAccountNumber = '';
        return;
      }

      this.state.showValidationErrors = false;

      if (this.requiresSmsVerification && !this.smsVerification.isVerified) {
        const smsWasSent = this.smsVerification.isCodeSent || (await this.requestSmsCode());
        this.state.submitSuccess = '';
        this.state.orderId = null;
        this.state.orderAccountNumber = '';

        if (smsWasSent) {
          this.state.submitError = '';
          this.openSmsModal();
        } else {
          this.state.submitError = this.smsVerification.error || 'Не удалось отправить SMS. Попробуйте позже';
        }

        return;
      }

      this.state.showSmsModal = false;
      this.state.isSubmitting = true;
      this.state.submitError = '';
      this.state.submitSuccess = '';
      this.state.orderId = null;
      this.state.orderAccountNumber = '';

      const unmaskedPhone =
        window.PhoneMask && typeof window.PhoneMask.unmask === 'function'
          ? window.PhoneMask.unmask(this.form.recipient.phone)
          : window.DevobPhoneMask && typeof window.DevobPhoneMask.getDigits === 'function'
            ? window.DevobPhoneMask.getDigits(this.form.recipient.phone)
            : this.getNormalizedPhone();

      const payload = {
        deliveryType: this.form.deliveryType,
        address: this.form.selectedAddress,
        terminal: this.form.terminal,
        courier: this.form.courier,
        paymentType: this.form.paymentType,
        recipient: {
          ...this.form.recipient,
          phone: unmaskedPhone  // Отправляем чистый номер
        }
      };

      const finalizeSubmit = () => {
        this.state.isSubmitting = false;
      };

      BX.ajax
          .runComponentAction('devob:order', 'createOrder', {
            mode: 'class',
            data: { form: payload }
          })
          .then((response) => {
            const data = response && response.data ? response.data : {};
            if (!data.success) {
              let errors = [];
              if (Array.isArray(data.errors)) {
                errors = data.errors;
              } else if (data.error) {
                errors = [data.error];
              }
              this.state.submitError = errors.join('. ');
              this.state.orderId = null;
              this.state.orderAccountNumber = '';
              finalizeSubmit();
              return;
            }
            this.state.submitSuccess = data.message || 'Заказ успешно оформлен';
            this.state.orderId = data.orderId || null;
            this.state.orderAccountNumber = data.accountNumber || '';
            this.state.orderDate = data.orderDate || new Date().toISOString();
            this.cart.items = [];
            this.cart.totalQty = 0;
            this.cart.totalSum = 0;
            this.cart.totalSumPrint = '0 ₽';
            this.basketValidation.valid = true;
            this.basketValidation.error = '';
            this.basketValidation.conflicts = null;
            this.state.showTerminalModal = false;
            if (window.BX && typeof BX.onCustomEvent === 'function') {
              BX.onCustomEvent('OnBasketChange');
            }

            // Перенаправление на оплату
            if (data.payment && data.payment.needRedirect && data.payment.url) {
              // Даем пользователю время увидеть сообщение об успехе
              setTimeout(() => {
                window.location.href = data.payment.url;
              }, 1500);
            } else {
              finalizeSubmit();
            }
          })
          .catch((error) => {
            console.error('Ошибка оформления заказа', error);
            this.state.submitError = 'Не удалось отправить заказ. Попробуйте позже';
            this.state.orderId = null;
            this.state.orderAccountNumber = '';
            finalizeSubmit();
          });
    }
  }
};
</script>
