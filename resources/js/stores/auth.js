import {defineStore} from 'pinia';
import {api, setCsrf} from '../lib/api';

let pending;
export const useAuthStore = defineStore('auth', {
    state: () => ({user: null, ready: false}),
    actions: {
        accept(data) {
            this.user = data.user;
            this.ready = true;
            setCsrf(data.csrf_token);
        },
        async init() {
            if (this.ready) return;
            if (!pending)
                pending = api
                    .get('/session')
                    .then(({data}) => this.accept(data))
                    .finally(() => {
                        pending = null;
                    });
            return pending;
        },
        async submit(mode, fields) {
            await this.init();
            const {data} = await api.post('/' + mode, fields);
            this.accept(data);
        },
        async logout() {
            const {data} = await api.post('/logout');
            this.accept(data);
        },
    },
});
