import {defineStore} from 'pinia';

export const useUiStore = defineStore('ui', {
    state: () => ({toasts: []}),
    actions: {
        notify(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({id, message, type});
            setTimeout(() => this.dismiss(id), 5000);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((item) => item.id !== id);
        },
    },
});
