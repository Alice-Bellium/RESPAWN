import {defineStore} from 'pinia';
import {readStorage, writeStorage} from '../lib/format';

const saved = readStorage('respawn.favorites', []);
export const useFavoritesStore = defineStore('favorites', {
    state: () => ({
        ids: Array.isArray(saved) ? [...new Set(saved.filter(Number.isInteger))].slice(0, 50) : [],
    }),
    actions: {
        toggle(id) {
            if (this.ids.includes(id)) this.ids = this.ids.filter((value) => value !== id);
            else if (this.ids.length < 50) this.ids.push(id);
            writeStorage('respawn.favorites', this.ids);
        },
    },
});
