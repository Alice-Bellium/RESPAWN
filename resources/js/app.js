import {createApp} from 'vue';
import {createPinia} from 'pinia';
import router from './router';
import App from './App.vue';
import {useUiStore} from './stores/ui';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.use(router);
router.onError((error) => {
    console.error(error);
    useUiStore(pinia).notify('Не удалось открыть страницу. Обновите её и попробуйте снова.', 'error');
});
app.mount('#app');
