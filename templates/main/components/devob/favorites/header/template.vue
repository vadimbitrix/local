<template>
  <div class="header__panel-btn" @click="goToFavorites($event)" @mouseenter="open" @mouseleave="close">
    <svg
        class="header__panel-btn-icon header__panel-btn-icon--favorite"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >
      <path
          d="M21 8.25C21 5.765 18.901 3.75 16.312 3.75C14.377 3.75 12.715 4.876 12 6.483C11.285 4.876 9.623 3.75 7.687 3.75C5.1 3.75 3 5.765 3 8.25C3 15.47 12 20.25 12 20.25C12 20.25 21 15.47 21 8.25Z"
          stroke="currentColor"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
      />
    </svg>
    <span class="header__panel-btn-text">Избранное</span>
    <span v-if="isFavoritesAuthorized && totalCount > 0" class="cart-counter">{{ totalCount }}</span>

    <template v-if="isFavoritesAuthorized && favoriteItems && favoriteItems.length > 0">
      <div v-if="show" class="cart-popup favorites-popup" @click.stop>
        <div class="cart-content">
          <div
              v-for="item in favoriteItems"
              :key="item.productId"
              class="cart-item"
          >
            <div class="cart-item-info">
              <div class="cart-item-name">
                <a v-if="item.url" :href="item.url" class="cart-item-name cart-item-name__link">{{ item.name }}</a>
                <span v-else>{{ item.name }}</span>
              </div>
              <div class="cart-item-sum" v-if="item.pricePrint" v-html="item.pricePrint"></div>
            </div>
            <div class="cart-item-actions">
              <button
                  class="cart-item-remove"
                  @click="remove(item.productId, $event)"
                  title="Удалить из избранного"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m18 6-12 12M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <a href="/personal/favorites/" class="cart-btn">Перейти в избранное</a>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'DevobFavoritesHeader',

  data() {
    return {
      show: false,
      removing: false
    };
  },

  setup() {
    if (!window.useFavoritesStore || !window.devobPinia) {
      return {
        favoriteItems: [],
        totalCount: 0,
        favorites: null,
        favoritesAuthorizedState: Vue.computed(() => false)
      };
    }

    const favorites = window.useFavoritesStore(window.devobPinia);

    const serverData = typeof window !== 'undefined' && window.BitrixVueData
        ? window.BitrixVueData['devob-favorites']
        : null;

    if (serverData && typeof favorites.load === 'function') {
      favorites.load(serverData);
    }

    if (window.Pinia && window.Pinia.storeToRefs) {
      const { items, total, isAuthorized } = window.Pinia.storeToRefs(favorites);
      return {
        favoriteItems: items,
        totalCount: total,
        favorites,
        favoritesAuthorizedState: isAuthorized
      };
    }

    return {
      favoriteItems: Vue.computed(() => favorites.items),
      totalCount: Vue.computed(() => favorites.total),
      favorites,
      favoritesAuthorizedState: Vue.computed(() => favorites.isAuthorized === true)
    };
  },

  computed: {
    isFavoritesAuthorized() {
      if (this.favorites && typeof this.favorites.ensureAuthorized === 'function') {
        return this.favorites.ensureAuthorized();
      }

      if (this.favoritesAuthorizedState && typeof this.favoritesAuthorizedState.value !== 'undefined') {
        return this.favoritesAuthorizedState.value === true;
      }

      return this.favorites?.isAuthorized === true;
    }
  },

  methods: {
    open() {
      if (!this.isFavoritesAuthorized) {
        this.show = false;
        return;
      }

      this.show = true;
    },

    close() {
      this.show = false;
    },

    goToFavorites(event) {
      if (!this.ensureAuthorized(event)) {
        return;
      }

      window.location.href = '/personal/favorites/';
    },

    remove(productId, event) {
      if (!this.favorites || this.removing) {
        return;
      }

      if (!this.ensureAuthorized(event)) {
        return;
      }

      this.removing = true;

      const finalize = () => {
        this.removing = false;
      };

      const requestOptions = this.buildFavoritesRequestOptions(productId);

      BX.ajax.runComponentAction('devob:favorites', 'remove', requestOptions)
          .then((response) => {
            if (response.data && response.data.success) {
              if (typeof this.favorites.load === 'function') {
                this.favorites.load(response.data);
              }
            }

            finalize();
          }, (error) => {
            console.error('Favorites remove error', error);
            finalize();
          });
    },

    buildFavoritesRequestOptions(productId) {
      const options = {
        mode: 'class',
        data: {
          productId
        }
      };

      const { signedParameters, hlBlockId } = this.resolveFavoritesMetadata();

      if (signedParameters) {
        options.signedParameters = signedParameters;
      }

      if (Number.isInteger(hlBlockId) && hlBlockId > 0) {
        options.componentParams = {
          ...(options.componentParams || {}),
          HL_BLOCK_ID: hlBlockId
        };
      }

      return options;
    },

    resolveFavoritesMetadata() {
      const sources = [];

      if (this.favorites && typeof this.favorites === 'object') {
        sources.push(this.favorites);
      }

      if (typeof window !== 'undefined' && window.BitrixVueData && window.BitrixVueData['devob-favorites']) {
        sources.push(window.BitrixVueData['devob-favorites']);
      }

      let signedParameters = null;
      let hlBlockId = null;

      const parseHlId = (value) => {
        const parsed = parseInt(value, 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
      };

      sources.forEach((source) => {
        if (!source) {
          return;
        }

        if (!signedParameters) {
          const signedCandidates = [
            source.signedParameters,
            source.signed_parameters,
            source.SIGNED_PARAMETERS,
            source.state?.signedParameters,
            source.$state?.signedParameters,
            source.favorites?.signedParameters
          ];

          const matchedSignature = signedCandidates.find((value) => typeof value === 'string' && value !== '');

          if (matchedSignature) {
            signedParameters = matchedSignature;
          }
        }

        if (!hlBlockId) {
          const hlCandidates = [
            source.hlBlockId,
            source.HL_BLOCK_ID,
            source.hl_block_id,
            source.state?.hlBlockId,
            source.$state?.hlBlockId,
            source.favorites?.hlBlockId
          ];

          const matchedHlId = hlCandidates
              .map(parseHlId)
              .find((value) => value !== null);

          if (matchedHlId) {
            hlBlockId = matchedHlId;
          }
        }
      });

      return { signedParameters, hlBlockId };
    },

    ensureAuthorized(event) {
      if (this.favorites && typeof this.favorites.ensureAuthorized === 'function') {
        const authorized = this.favorites.ensureAuthorized();
        if (!authorized) {
          if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
            event.stopPropagation?.();
          }

          if (window.devobAuthPopup && typeof window.devobAuthPopup.open === 'function') {
            window.devobAuthPopup.open('login');
          }

          return false;
        }

        return true;
      }

      return false;
    }
  }
};
</script>
