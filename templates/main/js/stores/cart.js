
const { defineStore } = Pinia;

const STORAGE_KEY = 'devob_cart';

const formatCurrency = (value) => {
    const number = Number(value);
    if (!Number.isFinite(number)) {
        return '0 ₽';
    }
    try {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            maximumFractionDigits: 0,
            minimumFractionDigits: 0
        }).format(number);
    } catch (e) {
        return `${Math.round(number)} ₽`;
    }
};

const readStoredCart = () => {
    if (typeof localStorage === 'undefined') {
        return null;
    }
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (!stored) { return null; }
        return JSON.parse(stored);
    } catch (e) {
        console.error('Ошибка чтения корзины из localStorage:', e);
        return null;
    }
};

const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        totalQty: 0,
        totalSum: 0,
        totalSumPrint: formatCurrency(0)
    }),

    getters: {
        // Проверяет есть ли товар в корзине
        isInCart: (state) => (productId) => {
            return state.items.some(item => item.productId == productId);
        },

        // Получает товар из корзины по ID
        getItem: (state) => (productId) => {
            return state.items.find(item => item.productId == productId);
        }
    },

    actions: {
        load(serverData = {}) {
            const stored = readStoredCart();
            const storedComments = {};
            if (stored && Array.isArray(stored.items)) {
                stored.items.forEach(item => {
                    if (item && item.productId !== undefined) {
                        storedComments[item.productId] = item.comment || '';
                    }
                });
            }

            const incomingItems = Array.isArray(serverData.items) ? serverData.items : [];

            this.items = incomingItems.map(item => {
                const productId = item.productId;
                const qtyRaw = parseInt(item.qty, 10);
                const qty = Number.isFinite(qtyRaw) && qtyRaw > 0 ? qtyRaw : 1;
                const priceRaw = parseFloat(item.price);
                const price = Number.isFinite(priceRaw) ? priceRaw : 0;
                const sumRaw = item.sum !== undefined ? parseFloat(item.sum) : price * qty;
                const sum = Number.isFinite(sumRaw) ? sumRaw : price * qty;
                const comment = item.comment !== undefined
                    ? item.comment
                    : (storedComments[productId] || '');

                return {
                    basketId: item.basketId ?? null,
                    productId,
                    name: item.name || `Товар ${productId}`,
                    qty,
                    price,
                    pricePrint: item.pricePrint || formatCurrency(price),
                    sum,
                    sumPrint: item.sumPrint || formatCurrency(sum),
                    url: item.url || '',
                    image: item.image || '',
                    comment
                };
            });

            this.recalculate();

            const totalQtyRaw = parseInt(serverData.totalQty, 10);
            if (Number.isFinite(totalQtyRaw)) {
                this.totalQty = totalQtyRaw;
            }

            const totalSumRaw = parseFloat(serverData.totalSum);
            if (Number.isFinite(totalSumRaw)) {
                this.totalSum = totalSumRaw;
            }

            if (typeof serverData.totalSumPrint === 'string' && serverData.totalSumPrint.trim()) {
                this.totalSumPrint = serverData.totalSumPrint;
            } else {
                this.totalSumPrint = formatCurrency(this.totalSum);
            }

            this.saveToStorage();
        },

        add(item) {
            const found = this.items.find(i => i.productId === item.productId);

            if (found) {
                console.warn('Товар уже в корзине');
                return false;
            }

            const price = parseFloat(item.price) || 0;

            this.items.push({
                basketId: null,
                productId: item.productId,
                qty: 1,
                name: item.name || `Товар ${item.productId}`,
                price,
                pricePrint: item.pricePrint || formatCurrency(price),
                sum: price,
                sumPrint: item.sumPrint || formatCurrency(price),
                url: item.url || '',
                image: item.image || '',
                comment: item.comment || ''
            });

            this.recalculate();
            this.saveToStorage();
            return true;
        },

        // Удаление товара по productId или basketId
        remove(productId, basketId = null) {
            let index = -1;

            if (basketId) {
                index = this.items.findIndex(item => item.basketId == basketId);
            } else {
                index = this.items.findIndex(item => item.productId == productId);
            }

            if (index !== -1) {
                this.items.splice(index, 1);
                this.recalculate();
                this.saveToStorage();
                return true;
            }
            return false;
        },

        recalculate() {
            let totalQty = 0;
            let totalSum = 0;

            this.items = this.items.map(item => {
                const qtyRaw = parseInt(item.qty, 10);
                const qty = Number.isFinite(qtyRaw) && qtyRaw > 0 ? qtyRaw : 1;
                const priceRaw = parseFloat(item.price);
                const price = Number.isFinite(priceRaw) ? priceRaw : 0;
                const sum = price * qty;

                totalQty += qty;
                totalSum += sum;

                return {
                    ...item,
                    qty,
                    price,
                    sum,
                    pricePrint: item.pricePrint || formatCurrency(price),
                    sumPrint: item.sumPrint || formatCurrency(sum)
                };
            });

            this.totalQty = totalQty;
            this.totalSum = totalSum;
            this.totalSumPrint = formatCurrency(totalSum);
        },

        saveToStorage() {
            if (typeof localStorage === 'undefined') {
                return;
            }

            try {
                const payload = {
                    items: this.items.map(item => ({
                        basketId: item.basketId,
                        productId: item.productId,
                        name: item.name,
                        qty: item.qty,
                        price: item.price,
                        pricePrint: item.pricePrint,
                        sum: item.sum,
                        sumPrint: item.sumPrint,
                        url: item.url,
                        image: item.image,
                        comment: item.comment || ''
                    })),
                    totalQty: this.totalQty,
                    totalSum: this.totalSum,
                    totalSumPrint: this.totalSumPrint,
                    timestamp: Date.now()
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
            } catch (e) {
                console.error('Ошибка сохранения корзины:', e);
            }
        },

        loadFromStorage(maxAge = 3600000) {
            if (typeof localStorage === 'undefined') {
                return false;
            }

            try {
                const stored = localStorage.getItem(STORAGE_KEY);
                if (stored) {
                    const data = JSON.parse(stored);
                    if (!data.timestamp || Date.now() - data.timestamp < maxAge) {
                        this.load(data);
                        return true;
                    }
                }
            } catch (e) {
                console.error('Ошибка загрузки корзины из localStorage:', e);
            }
            return false;
        },

        updateComment({ basketId = null, productId = null, comment = '' }) {
            const normalizedComment = typeof comment === 'string' ? comment : '';
            const target = this.items.find(item => {
                if (basketId) {
                    return item.basketId == basketId;
                }
                return item.productId == productId;
            });

            if (target) {
                target.comment = normalizedComment;
                this.saveToStorage();
            }
        }
    }
});

window.useCartStore = useCartStore;
