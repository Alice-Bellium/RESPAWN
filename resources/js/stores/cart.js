import {defineStore} from 'pinia';
import {api} from '../lib/api';
import {readStorage, writeStorage} from '../lib/format';

const stored = readStorage('respawn.cart', []);
const initial = Array.isArray(stored)
    ? stored
          .filter(
              (item) =>
                  Number.isInteger(item?.id) &&
                  item.id > 0 &&
                  Number.isInteger(item?.quantity) &&
                  item.quantity > 0 &&
                  item.quantity <= 20,
          )
          .slice(0, 50)
    : [];

export const useCartStore = defineStore('cart', {
    state: () => ({items: initial, products: [], loading: false, unavailable: []}),
    getters: {
        count: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
        lines: (state) =>
            state.items.map((item) => ({
                ...item,
                product: state.products.find((product) => product.id === item.id),
            })),
        subtotal() {
            return this.lines.reduce((sum, line) => sum + (line.product?.price || 0) * line.quantity, 0);
        },
        valid() {
            return (
                this.lines.length > 0 &&
                this.lines.every((line) => line.product && line.product.stock >= line.quantity)
            );
        },
    },
    actions: {
        persist() {
            writeStorage('respawn.cart', this.items);
        },
        add(product) {
            const item = this.items.find((item) => item.id === product.id);
            if (!product.stock || (item?.quantity || 0) >= Math.min(product.stock, 20)) return false;
            if (!this.products.some((item) => item.id === product.id)) this.products.push(product);
            if (item) item.quantity++;
            else if (this.items.length < 50) this.items.push({id: product.id, quantity: 1});
            else return false;
            this.persist();
            return true;
        },
        setQuantity(id, quantity) {
            const item = this.items.find((item) => item.id === id);
            if (item) item.quantity = Math.max(1, Math.min(20, Number(quantity) || 1));
            this.persist();
        },
        remove(id) {
            this.items = this.items.filter((item) => item.id !== id);
            this.persist();
        },
        clear() {
            this.items = [];
            this.products = [];
            this.persist();
        },
        async refresh() {
            this.loading = true;
            try {
                if (!this.items.length) {
                    this.products = [];
                    return;
                }
                const {data} = await api.get('/products', {
                    params: {ids: this.items.map((item) => item.id), per_page: 50},
                });
                this.products = data.data;
                this.unavailable = this.items.filter(
                    (item) => !this.products.some((product) => product.id === item.id),
                );
            } finally {
                this.loading = false;
            }
        },
    },
});
