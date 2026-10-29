<script setup>
import {onMounted} from 'vue';
import {X} from '@lucide/vue';
import SiteHeader from './components/SiteHeader.vue';
import SiteFooter from './components/SiteFooter.vue';
import {useAuthStore} from './stores/auth';
import {useUiStore} from './stores/ui';

const auth = useAuthStore();
const ui = useUiStore();

onMounted(() => {
    auth.init().catch(() =>
        ui.notify('Не удалось проверить вход. Обновите страницу перед оформлением.', 'error'),
    );
});
</script>

<template>
    <a
        href="#content"
        class="skip-link"
    >
        Перейти к содержимому
    </a>
    <div id="top"></div>
    <SiteHeader />
    <div id="content"><RouterView /></div>
    <SiteFooter />
    <div
        class="toast-stack"
        aria-live="polite"
        aria-atomic="false"
    >
        <div
            v-for="toast in ui.toasts"
            :key="toast.id"
            class="toast"
            :class="toast.type"
        >
            <span>{{ toast.message }}</span>
            <button
                class="icon-button"
                aria-label="Закрыть уведомление"
                @click="ui.dismiss(toast.id)"
            >
                <X :size="17" />
            </button>
        </div>
    </div>
</template>
