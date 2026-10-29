import test from 'node:test';
import assert from 'node:assert/strict';
import {createPinia, setActivePinia} from 'pinia';
import {useCartStore} from '../../resources/js/stores/cart.js';

test('restored cart rejects malformed entries and duplicate products', () => {
    globalThis.localStorage = {
        getItem: () =>
            JSON.stringify([
                {id: 1, quantity: 2},
                {id: 1, quantity: 3},
                {id: -2, quantity: 1},
                {id: 3, quantity: 0},
                {id: 4, quantity: 1.5},
                {id: 5, quantity: 100},
            ]),
        setItem() {},
    };
    setActivePinia(createPinia());
    assert.deepEqual(useCartStore().items, [{id: 1, quantity: 3}]);
});

test('separate store instances do not share mutable arrays', () => {
    globalThis.localStorage = {getItem: () => null, setItem() {}};
    setActivePinia(createPinia());
    useCartStore().add({id: 1, price: 100, stock: 5});
    setActivePinia(createPinia());
    assert.equal(useCartStore().count, 0);
});
