<script setup>
import {onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import {LogOut, ArrowUpRight} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import {date, money, statusLabels} from '../lib/format';
import {useAuthStore} from '../stores/auth';
import {useUiStore} from '../stores/ui';
import Pagination from '../components/Pagination.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
const auth = useAuthStore();
const ui = useUiStore();
const router = useRouter();
const tab = ref('orders');
const orders = ref([]);
const meta = ref({});
const name = ref(auth.user?.name || '');
const passwords = ref({current_password: '', password: '', password_confirmation: ''});
const loading = ref(false);
const busy = ref(false);
const error = ref('');
async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const {data} = await api.get('/orders', {params: {page}});
        orders.value = data.data;
        meta.value = data.meta;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
}
onMounted(() => load());
async function save(mode) {
    if (busy.value) return;
    busy.value = true;
    error.value = '';
    try {
        const {data} =
            mode === 'profile'
                ? await api.patch('/profile', {name: name.value})
                : await api.put('/password', passwords.value);
        auth.accept(data);
        passwords.value = {current_password: '', password: '', password_confirmation: ''};
        ui.notify(mode === 'profile' ? 'Имя обновлено' : 'Пароль обновлён');
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        busy.value = false;
    }
}
async function logout() {
    try {
        await auth.logout();
        await router.push('/');
    } catch (e) {
        ui.notify(errorMessage(e), 'error');
    }
}
</script>
<template>
    <main class="container page">
        <div class="page-heading">
            <div class="account-header">
                <div class="avatar">{{ auth.user?.name?.charAt(0).toUpperCase() }}</div>
                <div>
                    <span class="eyebrow accent">ТВОЙ RESPAWN</span>
                    <h1 style="font-family: Manrope, sans-serif; font-size: 30px; margin-top: 6px">
                        Привет, {{ auth.user?.name }}
                    </h1>
                </div>
            </div>
            <button
                class="button secondary compact"
                @click="logout"
            >
                <LogOut :size="15" />
                Выйти
            </button>
        </div>
        <RouterLink
            v-if="auth.user?.is_admin"
            to="/admin"
            class="button secondary compact"
            style="margin-bottom: 25px"
        >
            Управление магазином
            <ArrowUpRight :size="16" />
        </RouterLink>
        <nav
            class="tabs"
            aria-label="Разделы кабинета"
        >
            <button
                :class="{active: tab === 'orders'}"
                @click="
                    tab = 'orders';
                    error = '';
                "
            >
                Мои заказы
            </button>
            <button
                :class="{active: tab === 'profile'}"
                @click="
                    tab = 'profile';
                    error = '';
                "
            >
                Профиль и безопасность
            </button>
        </nav>
        <p
            v-if="error"
            role="alert"
            class="alert error"
        >
            {{ error }}
        </p>
        <template v-if="tab === 'orders'">
            <LoadingState v-if="loading" />
            <EmptyState
                v-else-if="!orders.length"
                title="Первый заказ впереди"
                text="Здесь появятся все твои покупки и их статусы."
            >
                <RouterLink
                    to="/catalog"
                    class="button primary"
                >
                    Открыть каталог
                </RouterLink>
            </EmptyState>
            <article
                v-for="order in orders"
                :key="order.id"
                class="order-card"
            >
                <div class="order-card-top">
                    <div>
                        <h3>Заказ от {{ date(order.created_at) }}</h3>
                        <span class="order-number">{{ order.number }}</span>
                    </div>
                    <span
                        class="order-status"
                        :class="order.status"
                    >
                        {{ statusLabels[order.status] }}
                    </span>
                </div>
                <p
                    class="muted"
                    style="margin: 20px 0 0; font-size: 12px"
                >
                    {{ order.items.map((item) => item.name + ' × ' + item.quantity).join(', ') }}
                </p>
                <div class="order-card-bottom">
                    <strong class="price">{{ money(order.total) }}</strong>
                    <RouterLink
                        :to="'/orders/' + order.number"
                        class="button secondary compact"
                    >
                        Подробнее
                        <ArrowUpRight :size="16" />
                    </RouterLink>
                </div>
            </article>
            <Pagination
                :page="meta.current_page"
                :last="meta.last_page"
                @change="load"
            />
        </template>
        <div
            v-else
            class="form-grid"
        >
            <form
                class="form-card form-stack"
                @submit.prevent="save('profile')"
            >
                <h2>Основная информация</h2>
                <label class="field-label">
                    Имя
                    <input
                        v-model="name"
                        required
                        maxlength="100"
                        autocomplete="name"
                    />
                </label>
                <label class="field-label">
                    Электронная почта
                    <input
                        :value="auth.user?.email"
                        disabled
                        type="email"
                    />
                </label>
                <button
                    class="button primary"
                    :disabled="busy"
                >
                    Сохранить имя
                </button>
            </form>
            <form
                class="form-card form-stack"
                @submit.prevent="save('password')"
            >
                <h2>Смена пароля</h2>
                <label class="field-label">
                    Текущий пароль
                    <input
                        v-model="passwords.current_password"
                        type="password"
                        required
                        autocomplete="current-password"
                        maxlength="72"
                    />
                </label>
                <label class="field-label">
                    Новый пароль
                    <input
                        v-model="passwords.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        minlength="10"
                        maxlength="72"
                    />
                </label>
                <label class="field-label">
                    Повтори новый пароль
                    <input
                        v-model="passwords.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        minlength="10"
                        maxlength="72"
                    />
                </label>
                <p
                    class="muted"
                    style="font-size: 11px"
                >
                    Не менее 10 символов, включая буквы и цифры.
                </p>
                <button
                    class="button secondary"
                    :disabled="busy"
                >
                    Обновить пароль
                </button>
            </form>
        </div>
    </main>
</template>
