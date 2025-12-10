<template>
  <div class="header__panel-btn" @mouseenter="open" @mouseleave="close" @click="goToCart">
    <svg class="header__panel-btn-icon header__panel-btn-icon--cart" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path
          d="M2.25 3H3.636C4.146 3 4.591 3.343 4.723 3.835L5.106 5.272M5.106 5.272C10.6766 5.11589 16.2419 5.73515 21.642 7.112C20.818 9.566 19.839 11.95 18.718 14.25H7.5M5.106 5.272L7.5 14.25M7.5 14.25C6.70435 14.25 5.94129 14.5661 5.37868 15.1287C4.81607 15.6913 4.5 16.4544 4.5 17.25H20.25M6 20.25C6 20.4489 5.92098 20.6397 5.78033 20.7803C5.63968 20.921 5.44891 21 5.25 21C5.05109 21 4.86032 20.921 4.71967 20.7803C4.57902 20.6397 4.5 20.4489 4.5 20.25C4.5 20.0511 4.57902 19.8603 4.71967 19.7197C4.86032 19.579 5.05109 19.5 5.25 19.5C5.44891 19.5 5.63968 19.579 5.78033 19.7197C5.92098 19.8603 6 20.0511 6 20.25ZM18.75 20.25C18.75 20.4489 18.671 20.6397 18.5303 20.7803C18.3897 20.921 18.1989 21 18 21C17.8011 21 17.6103 20.921 17.4697 20.7803C17.329 20.6397 17.25 20.4489 17.25 20.25C17.25 20.0511 17.329 19.8603 17.4697 19.7197C17.6103 19.579 17.8011 19.5 18 19.5C18.1989 19.5 18.3897 19.579 18.5303 19.7197C18.671 19.8603 18.75 20.0511 18.75 20.25Z"
          stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="header__panel-btn-text">Корзина</span>

    <span v-if="storeItems.length > 0" class="cart-counter">{{ storeTotalQty }}</span>

    <template v-if="storeItems && storeItems.length > 0">
      <div v-if="show" class="cart-popup" @click.stop>
        <div class="cart-content">
          <div v-for="item in storeItems" :key="item.basketId || item.productId" class="cart-item">
            <div class="cart-item-info">
              <div class="cart-item-name">
                <a v-if="item.url" :href="item.url" class="cart-item-name cart-item-name__link">{{ item.name }}</a>
                <span v-else>{{ item.name }}</span>
              </div>
            </div>
            <div class="cart-item-actions">
              <div class="cart-item-sum" v-html="item.pricePrint"></div>
              <button
                  class="cart-item-remove"
                  @click="removeItem(item.basketId)"
                  title="Удалить товар"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m18 6-12 12M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="cart-total">
              <span>Итого:</span>
              <span v-html="storeTotalSumPrint"></span>
          </div>

          <a href="/cart/" class="cart-btn">Перейти в корзину</a>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'DevobSmallCart',
  data() {
    return {
      show: false,
      removing: false
    };
  },

  setup() {
    if (!window.useCartStore || !window.devobPinia) {
      return {
        storeItems: [],
        storeTotalQty: 0,
        storeTotalSum: 0,
        storeTotalSumPrint: 0,
        cart: null
      };
    }

    const cart = window.useCartStore(window.devobPinia);

    // Инициализируем store данными из PHP
    if (cart.items.length === 0) {
      const phpItems = typeof window !== 'undefined' && window.BitrixVueData ?
          window.BitrixVueData['devob-small-cart']?.items : [];
      const phpTotalQty = typeof window !== 'undefined' && window.BitrixVueData ?
          window.BitrixVueData['devob-small-cart']?.totalqty : 0;
      const phpTotalSum = typeof window !== 'undefined' && window.BitrixVueData ?
          window.BitrixVueData['devob-small-cart']?.totalsum : 0;

      if (phpItems && phpItems.length > 0) {
        cart.load({
          items: phpItems,
          totalQty: phpTotalQty || 0,
          totalSum: phpTotalSum || 0
        });
      }
    }

    // Используем storeToRefs для реактивности
    if (window.Pinia && window.Pinia.storeToRefs) {
      const {items: storeItems, totalQty: storeTotalQty, totalSum: storeTotalSum, totalSumPrint: storeTotalSumPrint} = window.Pinia.storeToRefs(cart);
      return {
        storeItems,
        storeTotalQty,
        storeTotalSum,
        storeTotalSumPrint,
        cart
      };
    } else {
      return {
        storeItems: Vue.computed(() => cart.items),
        storeTotalQty: Vue.computed(() => cart.totalQty),
        storeTotalSum: Vue.computed(() => cart.totalSum),
        storeTotalSumPrint: Vue.computed(() => cart.totalSumPrint),
        cart
      };
    }
  },

  methods: {
    open() {
      this.show = true;
    },

    close() {
      this.show = false;
    },

    goToCart() {
      window.location.href = '/cart/';
    },

    async removeItem(basketId) {
      if (this.removing) return;

      this.removing = true;

      try {
        const response = await BX.ajax.runComponentAction('devob:cart', 'remove', {
          mode: 'class',
          data: {basketItemId: basketId}
        });

        if (response.data && response.data.success) {
          // Обновляем store с новыми данными с сервера
          this.cart.load({
            items: response.data.items || [],
            totalQty: response.data.totalQty || 0,
            totalSum: response.data.totalSum || 0
          });
        } else {
          console.error('Ошибка удаления:', response.data?.error);
        }

      } catch (error) {
        console.error('Ошибка сети при удалении:', error);
      } finally {
        this.removing = false;
      }
    }
  }
};
</script>
