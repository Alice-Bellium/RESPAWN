import {defineStore} from 'pinia';
import {readStorage, writeStorage} from '../lib/format.js';

export const useFavoritesStore = defineStore('favorites', {
    state: () => {
        const saved = readStorage('respawn.favorites', []);
        return {
            ids: Array.isArray(saved)
                ? [...new Set(saved.filter((id) => Number.isInteger(id) && id > 0))].slice(0, 50)
                : [],
        };
    },
    actions: {
        toggle(id) {
            if (this.ids.includes(id)) this.ids = this.ids.filter((value) => value !== id);
            else if (this.ids.length < 50) this.ids.push(id);
            writeStorage('respawn.favorites', this.ids);
        },
    },
});
