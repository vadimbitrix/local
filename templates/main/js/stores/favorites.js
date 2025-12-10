const normalizeIds = (ids) => Array.from(new Set(
    ids
        .map((id) => parseInt(id, 10))
        .filter((id) => Number.isFinite(id) && id > 0)
));

const extractIsAuthorized = (payload) => {
    if (!payload || typeof payload !== 'object') {
        return null;
    }

    if (Object.prototype.hasOwnProperty.call(payload, 'IS_AUTHORIZED')) {
        return Boolean(payload.IS_AUTHORIZED);
    }

    if (Object.prototype.hasOwnProperty.call(payload, 'isAuthorized')) {
        return Boolean(payload.isAuthorized);
    }

    if (Object.prototype.hasOwnProperty.call(payload, 'is_authorized')) {
        return Boolean(payload.is_authorized);
    }

    return null;
};

const extractSignedParameters = (payload) => {
    if (!payload || typeof payload !== 'object') {
        return null;
    }

    const candidates = [
        payload.signedParameters,
        payload.signed_parameters,
        payload.SIGNED_PARAMETERS,
        payload.signedParams,
        payload.signed_params
    ];

    for (const candidate of candidates) {
        if (typeof candidate === 'string' && candidate !== '') {
            return candidate;
        }
    }

    return null;
};

const normalizeItem = (item) => {
    if (!item) {
        return null;
    }

    const productId = parseInt(item.productId || item.ID || item.id, 10);
    if (!productId) {
        return null;
    }

    return {
        productId,
        name: item.name || item.NAME || `Товар ${productId}`,
        price: typeof item.price === 'number' ? item.price : parseFloat(item.price || 0),
        pricePrint: item.pricePrint || item.PRICE_PRINT || '',
        url: item.url || item.DETAIL_PAGE_URL || '#',
        image: item.image || item.PICTURE || item.IMAGE || '/upload/no-photo-white.png'
    };
};

const useFavoritesStore = defineStore('favorites', {
    state: () => ({
        items: [],
        ids: [],
        total: 0,
        lastSync: null,
        signedParameters: null,
        isAuthorized: false,
        hlBlockId: 0
    }),

    getters: {
        isFavorite: (state) => (productId) => {
            productId = parseInt(productId, 10);
            return state.ids.includes(productId);
        },

        getItem: (state) => (productId) => {
            productId = parseInt(productId, 10);
            return state.items.find((item) => item.productId === productId) || null;
        }
    },

    actions: {
        load(payload = {}) {
            const isAuthorized = extractIsAuthorized(payload);
            if (isAuthorized !== null) {
                this.isAuthorized = isAuthorized;
            }

            const rawHlBlockId = payload.hlBlockId || payload.HL_BLOCK_ID || payload.hl_block_id;
            const parsedHlBlockId = parseInt(rawHlBlockId, 10);
            if (Number.isFinite(parsedHlBlockId) && parsedHlBlockId > 0) {
                this.hlBlockId = parsedHlBlockId;
            }

            if (!this.ensureAuthorized()) {
                this.items = [];
                this.ids = [];
                this.total = 0;
                return;
            }

            const items = Array.isArray(payload.items) ? payload.items : [];
            const normalized = items.map(normalizeItem).filter(Boolean);

            this.items = normalized;
            this.ids = normalized.map((item) => item.productId);
            this.total = this.ids.length;
            this.lastSync = Date.now();
            const signedParameters = extractSignedParameters(payload);
            if (signedParameters) {
                this.signedParameters = signedParameters;
            }
        },

        add(item) {
            if (!this.ensureAuthorized()) {
                return false;
            }

            const normalized = normalizeItem(item);
            if (!normalized) {
                return false;
            }

            if (!this.ids.includes(normalized.productId)) {
                this.items.push(normalized);
                this.ids.push(normalized.productId);
                this.total = this.ids.length;
            }
            return true;
        },

        remove(productId) {
            if (!this.ensureAuthorized()) {
                return false;
            }

            productId = parseInt(productId, 10);
            const index = this.items.findIndex((item) => item.productId === productId);

            if (index !== -1) {
                this.items.splice(index, 1);
                this.ids = this.items.map((item) => item.productId);
                this.total = this.ids.length;
                return true;
            }
            return false;
        },

        setIds(productIds = []) {
            if (!this.ensureAuthorized()) {
                return;
            }

            const ids = normalizeIds(productIds);
            this.ids = ids;
            this.total = ids.length;
            this.items = this.items.filter((item) => ids.includes(item.productId));
        },

        merge(payload = {}) {
            if (!this.ensureAuthorized()) {
                return;
            }

            const items = Array.isArray(payload.items) ? payload.items : [];
            items.forEach((item) => {
                const normalized = normalizeItem(item);
                if (!normalized) {
                    return;
                }

                const index = this.items.findIndex((fav) => fav.productId === normalized.productId);
                if (index === -1) {
                    this.items.push(normalized);
                } else {
                    this.items.splice(index, 1, normalized);
                }
            });
            this.ids = this.items.map((item) => item.productId);
            this.total = this.ids.length;
            this.lastSync = Date.now();
            const signedParameters = extractSignedParameters(payload);
            if (signedParameters) {
                this.signedParameters = signedParameters;
            }

            const rawHlBlockId = payload.hlBlockId || payload.HL_BLOCK_ID || payload.hl_block_id;
            const parsedHlBlockId = parseInt(rawHlBlockId, 10);
            if (Number.isFinite(parsedHlBlockId) && parsedHlBlockId > 0) {
                this.hlBlockId = parsedHlBlockId;
            }
        },

        ensureAuthorized() {
            return this.isAuthorized === true;
        }
    }
});

window.useFavoritesStore = useFavoritesStore;
