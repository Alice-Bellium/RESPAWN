import test, {beforeEach} from 'node:test';
import assert from 'node:assert/strict';
import {createPinia, setActivePinia} from 'pinia';
import {useCartStore} from '../../resources/js/stores/cart.js';
import {useFavoritesStore} from '../../resources/js/stores/favorites.js';
import {api} from '../../resources/js/lib/api.js';
import {money, readStorage, safeRedirect, writeStorage} from '../../resources/js/lib/format.js';

beforeEach(() => {
    setActivePinia(createPinia());
    const values = new Map();
    globalThis.localStorage = {
        getItem: (key) => values.get(key) ?? null,
        setItem: (key, value) => values.set(key, value),
    };
});

const product = {id: 1, name: 'Demo mouse', price: 199050, stock: 2};

test('cart limits quantities to stock and persists identifiers without customer data', () => {
    const cart = useCartStore();
    assert.equal(cart.add(product), true);
    assert.equal(cart.add(product), true);
    assert.equal(cart.add(product), false);
    assert.equal(cart.count, 2);
    assert.equal(cart.subtotal, 398100);
    assert.deepEqual(JSON.parse(localStorage.getItem('respawn.cart')), [{id: 1, quantity: 2}]);
    cart.remove(1);
    assert.equal(cart.count, 0);
});

test('refresh notices changed prices, insufficient stock and missing products', async () => {
    const cart = useCartStore();
    cart.add(product);
    const original = api.get;
    try {
        api.get = async () => ({data: {data: [{...product, stock: 0, price: 250000}]}});
        await cart.refresh();
        assert.equal(cart.subtotal, 250000);
        assert.equal(cart.valid, false);
        api.get = async () => ({data: {data: []}});
        await cart.refresh();
        assert.equal(cart.unavailable.length, 1);
        assert.equal(cart.valid, false);
    } finally {
        api.get = original;
    }
});

test('favorites toggle without duplicate identifiers', () => {
    const favorites = useFavoritesStore();
    favorites.toggle(1);
    favorites.toggle(2);
    favorites.toggle(1);
    assert.deepEqual(favorites.ids, [2]);
});

test('storage failures and malformed JSON do not break the storefront', () => {
    localStorage.setItem('bad', '{broken');
    assert.deepEqual(readStorage('bad', []), []);
    globalThis.localStorage = {
        getItem() {
            throw Error('disabled');
        },
        setItem() {
            throw Error('full');
        },
    };
    assert.equal(readStorage('key', 'fallback'), 'fallback');
    assert.doesNotThrow(() => writeStorage('key', {}));
});

test('login redirect stays on this site and prices retain kopeks', () => {
    assert.equal(safeRedirect('https://other.test'), '/account');
    assert.equal(safeRedirect('//other.test'), '/account');
    assert.equal(safeRedirect('/\\other.test'), '/account');
    assert.equal(safeRedirect('/checkout'), '/checkout');
    assert.match(money(199050), /1\D990,5/);
});
