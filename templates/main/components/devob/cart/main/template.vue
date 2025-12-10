<template>
  <div class="full-cart-page">
    <div v-if="!hasItems && !hasRemoved" class="full-cart-page__empty">
      <div class="full-cart-empty">
        <div class="full-cart-empty__icon" aria-hidden="true">
          <svg width="161" height="160" viewBox="0 0 161 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M102.94 22.9199L124.346 59.9999H147.166V73.3333H139.386L134.34 133.887C134.201 135.553 133.441 137.106 132.212 138.238C130.982 139.37 129.371 139.999 127.7 140H33.2997C31.628 139.999 30.0176 139.37 28.7878 138.238C27.558 137.106 26.7984 135.553 26.6597 133.887L21.6063 73.3333H13.833V59.9999H36.6463L58.0597 22.9199L69.6063 29.5866L52.0463 59.9999H108.946L91.393 29.5866L102.94 22.9199ZM126.006 73.3333H34.9863L39.433 126.667H121.56L126.006 73.3333ZM87.1663 86.6666V113.333H73.833V86.6666H87.1663ZM60.4997 86.6666V113.333H47.1663V86.6666H60.4997ZM113.833 86.6666V113.333H100.5V86.6666H113.833Z"
              fill="#F7F7F7"
            />
          </svg>
        </div>
        <h1 class="full-cart-empty__title">Ваша корзина пуста</h1>
        <p class="full-cart-empty__text">Воспользуйтесь поиском, чтобы найти всё что нужно</p>
        <a href="/catalog/" class="full-cart-empty__button">Начать покупки</a>
      </div>
    </div>
    <div v-else class="full-cart-page__layout">
      <section class="full-cart-page__items">
        <template v-if="cart.items.length">
          <article
            v-for="item in cart.items"
            :key="itemKey(item)"
            class="full-cart-item"
          >
            <div class="full-cart-item__body">
              <div class="full-cart-item__media">
                <component :is="item.url ? 'a' : 'div'" :href="item.url || undefined" class="full-cart-item__image-link">
                  <img
                    :src="item.image"
                    :alt="item.name"
                    loading="lazy"
                    class="full-cart-item__image"
                  />
                </component>
              </div>
              <div class="full-cart-item__content">
                <div class="full-cart-item__header">
                  <div class="full-cart-item__title">
                    <a v-if="item.url" :href="item.url" class="full-cart-item__link">{{ item.name }}</a>
                    <span v-else>{{ item.name }}</span>
                  </div>
                  <button
                    class="full-cart-item__remove"
                    type="button"
                    @click="removeItem(item)"
                    :disabled="isRemoving(item)"
                    title="Удалить товар из корзины"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>

                  </button>
                </div>

                <div class="full-cart-item__controls">
                  <div class="full-cart-item__prices">
                    <div class="full-cart-item__price" v-html="item.pricePrint"></div>
                  </div>
                </div>

                <div class="full-cart-item__comment">
                  <label class="full-cart-item__comment-label" :for="'full-cart-comment-' + itemKey(item)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    Пожелание к видеообзору
                  </label>
                  <textarea
                    class="full-cart-item__comment-field"
                    :id="'cart-comment-' + itemKey(item)"
                    rows="2"
                    :placeholder="commentPlaceholder"
                    v-model="comments[itemKey(item)]"
                    @input="onCommentInput(item, comments[itemKey(item)])"
                  ></textarea>
                </div>
              </div>
            </div>
          </article>
        </template>
        <p v-else class="full-cart-page__empty-inline">В корзине нет активных товаров</p>

        <article
          v-for="entry in removedItems"
          :key="entry.id"
          class="full-cart-item full-cart-item--removed"
        >
          <div class="full-cart-item__removed">
            <div class="full-cart-item__removed-info">
              <div class="full-cart-item__removed-title">{{ entry.item.name }}</div>
              <p class="full-cart-item__removed-text">Товар удалён из корзины.</p>
            </div>
            <div class="full-cart-item__removed-actions">
              <button
                type="button"
                class="full-cart-item__restore"
                @click="restoreItem(entry)"
                :disabled="isRestoring(entry)"
              >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.99 7.5 8.24 3.75m0 0L4.49 7.5m3.75-3.75v16.499h11.25" />
                </svg>
                 Вернуть
              </button>
              <button
                type="button"
                class="full-cart-item__remove full-cart-item__remove--ghost"
                @click="forgetRemoved(entry)"
              >
                Скрыть
              </button>
            </div>
          </div>
        </article>
      </section>

      <aside v-if="hasItems" class="full-cart-page__summary">
        <div class="full-cart-summary">
          <h2 class="full-cart-summary__title">Оформление заказа</h2>
          <p class="full-cart-summary__note">
            Оплата заказа производится только после получения видеообзора и принятия окончательного решения о покупке.
          </p>
          <dl class="full-cart-summary__line">
            <dt>
              Товары
              <span class="full-cart-summary__count">({{ cart.totalQty }})</span>
            </dt>
            <dd v-html="cart.totalSumPrint"></dd>
          </dl>
          <dl class="full-cart-summary__line">
            <dt>Оплата</dt>
            <dd>Картой онлайн</dd>
          </dl>
          <dl class="full-cart-summary__total">
            <dt>Итого</dt>
            <dd v-html="cart.totalSumPrint"></dd>
          </dl>
          <a href="/cart/checkout/" class="full-cart-summary__submit">Оформить заказ</a>
        </div>
      </aside>
    </div>
  </div>
</template>

<script>

export default {
  name: 'DevobCartFull',
  setup() {
    const placeholderText = 'Например: расскажите о состоянии корпуса, комплектации и т.д.';
    if (typeof Vue === 'undefined' || !window.useCartStore || !window.devobPinia) {
      console.error('❌ useCartStore или Vue не найдены!');
      return {
        cart: { items: [], totalQty: 0, totalSum: 0 },
        hasItems: false,
        hasRemoved: false,
        comments: {},
        removedItems: [],
        commentPlaceholder: placeholderText,
        removeItem: () => {},
        restoreItem: () => {},
        forgetRemoved: () => {},
        onCommentInput: () => {},
        isRemoving: () => false,
        isRestoring: () => false,
        itemKey: (item) => String(item?.basketId || item?.productId || Math.random())
      };
    }

    const cart = window.useCartStore(window.devobPinia);

    if (typeof cart.loadFromStorage === 'function') {
      cart.loadFromStorage();
    }

    if (!cart.items.length) {
      BX.ajax.runComponentAction('devob:cart', 'getCart')
        .then((response) => {
          if (response?.data) {
            cart.load(response.data);
          }
        })
        .catch((error) => console.error('Ошибка загрузки корзины:', error));
    }

    const comments = Vue.reactive({});
    const removedItems = Vue.ref([]);
    const loadingStates = Vue.reactive({
      remove: {},
      restore: {}
    });

    const commentPlaceholder = placeholderText;
    const itemKey = (item) => String(item?.basketId || item?.productId || 'temp');

    const setLoading = (group, key, value) => {
      if (!loadingStates[group]) {
        loadingStates[group] = {};
      }
      if (value) {
        loadingStates[group][key] = true;
      } else {
        delete loadingStates[group][key];
      }
    };

    const syncComments = (items) => {
      const activeKeys = {};
      items.forEach(({ key, comment }) => {
        activeKeys[key] = true;
        comments[key] = comment || '';
      });

      Object.keys(comments).forEach((key) => {
        if (!activeKeys[key]) {
          delete comments[key];
        }
      });
    };

    Vue.watch(
      () => cart.items.map((item) => ({
        key: itemKey(item),
        comment: item.comment || ''
      })),
      (items) => {
        syncComments(items);
      },
      { immediate: true }
    );

    Vue.watch(
      () => cart.items.map((item) => item.productId),
      (productIds) => {
        removedItems.value = removedItems.value.filter((entry) => !productIds.includes(entry.item.productId));
      },
      { immediate: true }
    );

    const hasItems = Vue.computed(() => cart.items.length > 0);
    const hasRemoved = Vue.computed(() => removedItems.value.length > 0);

    const isRemoving = (item) => Boolean(loadingStates.remove[itemKey(item)]);
    const isRestoring = (entry) => Boolean(loadingStates.restore[entry.id]);

    const onCommentInput = (item, value) => {
      const key = itemKey(item);
      comments[key] = value;
      if (typeof cart.updateComment === 'function') {
        cart.updateComment({
          basketId: item.basketId,
          productId: item.productId,
          comment: value
        });
      }
    };

    const registerRemoved = (item) => {
      const entry = {
        id: `${item.productId}-${Date.now()}`,
        item: {
          ...item,
          comment: comments[itemKey(item)] || item.comment || ''
        }
      };
      removedItems.value = removedItems.value.filter((existing) => existing.item.productId !== item.productId);
      removedItems.value.unshift(entry);
    };

    const removeItem = async (item) => {
      const key = itemKey(item);
      if (!item?.basketId || isRemoving(item)) return;

      setLoading('remove', key, true);
      try {
        const response = await BX.ajax.runComponentAction('devob:cart', 'remove', {
          mode: 'class',
          data: { basketItemId: item.basketId }
        });
        if (response?.data?.success) {
          registerRemoved(item);
          cart.load(response.data);
        }
      } catch (error) {
        console.error('Ошибка удаления товара из корзины:', error);
      } finally {
        setLoading('remove', key, false);
      }
    };

    const forgetRemoved = (entry) => {
      removedItems.value = removedItems.value.filter((item) => item.id !== entry.id);
    };

    const restoreItem = async (entry) => {
      if (!entry?.item?.productId || isRestoring(entry)) return;

      setLoading('restore', entry.id, true);
      try {
        const response = await BX.ajax.runComponentAction('devob:cart', 'addToCart', {
          mode: 'class',
          data: {
            productId: entry.item.productId,
            qty: 1
          }
        });
        if (response?.data?.success) {
          cart.load(response.data);
          Vue.nextTick(() => {
            if (typeof cart.updateComment === 'function' && entry.item.comment) {
              cart.updateComment({
                productId: entry.item.productId,
                comment: entry.item.comment
              });
            }
          });
          forgetRemoved(entry);
        }
      } catch (error) {
        console.error('Ошибка восстановления товара:', error);
      } finally {
        setLoading('restore', entry.id, false);
      }
    };

    return {
      cart,
      comments,
      removedItems,
      hasItems,
      hasRemoved,
      commentPlaceholder,
      removeItem,
      restoreItem,
      forgetRemoved,
      onCommentInput,
      isRemoving,
      isRestoring,
      itemKey
    };
  }
};

</script>
