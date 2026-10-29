<script setup>
import {computed, onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import {LockKeyhole, ArrowRight} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import {money} from '../lib/format';
import {useCartStore} from '../stores/cart';
import {useAuthStore} from '../stores/auth';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
const cart = useCartStore();
const auth = useAuthStore();
const router = useRouter();
const loading = ref(true);
const busy = ref(false);
const error = ref('');
const settings = ref(null);
const form = ref({
    customer_name: auth.user?.name || '',
    email: auth.user?.email || '',
    phone: '',
    delivery_method: 'delivery',
    address: '',
    note: '',
});
let attempt = null;
let fingerprint = null;
const shipping = computed(() =>
    form.value.delivery_method === 'pickup' ||
    cart.subtotal >= (settings.value?.free_delivery_from || Infinity)
        ? 0
        : settings.value?.delivery_price || 0,
);
const total = computed(() => cart.subtotal + shipping.value);
onMounted(async () => {
    try {
        const [, response] = await Promise.all([cart.refresh(), api.get('/shop')]);
        settings.value = response.data;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
});
async function submit() {
    if (busy.value || !cart.valid || !settings.value) return;
    busy.value = true;
    error.value = '';
    const fields = {
        ...form.value,
        items: cart.items.map((item) => ({product_id: item.id, quantity: item.quantity})),
    };
    const current = JSON.stringify(fields);
    if (fingerprint !== current) {
        attempt = crypto.randomUUID();
        fingerprint = current;
    }
    try {
        const {data} = await api.post('/orders', {...fields, idempotency_key: attempt});
        cart.clear();
        await router.replace({path: '/orders/' + data.data.number, query: {placed: '1'}});
    } catch (e) {
        error.value = errorMessage(e);
        if (e.response?.status === 401) await router.push('/login?redirect=/checkout');
        if (e.response?.status === 422) await cart.refresh().catch(() => {});
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <main class="container page">
        <div class="breadcrumb">
            <RouterLink to="/cart">Корзина</RouterLink>
            <span>/</span>
            <span>Оформление</span>
        </div>
        <div class="page-heading">
            <div>
                <span class="eyebrow accent">ЕЩЁ ОДИН ШАГ</span>
                <h1>ОФОРМЛЕНИЕ ЗАКАЗА</h1>
            </div>
        </div>
        <LoadingState v-if="loading" />
        <EmptyState
            v-else-if="!cart.items.length"
            title="В корзине пока пусто"
            text="Сначала выбери оборудование."
        >
            <RouterLink
                to="/catalog"
                class="button primary"
            >
                В каталог
            </RouterLink>
        </EmptyState>
        <form
            v-else
            class="checkout-layout"
            @submit.prevent="submit"
        >
            <div>
                <section class="form-card">
                    <h2>
                        <span class="step-number">01</span>
                        Контактные данные
                    </h2>
                    <div class="form-stack">
                        <label class="field-label">
                            Имя получателя
                            <input
                                v-model="form.customer_name"
                                required
                                autocomplete="name"
                                maxlength="100"
                            />
                        </label>
                        <div class="form-grid">
                            <label class="field-label">
                                Электронная почта
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    maxlength="190"
                                />
                            </label>
                            <label class="field-label">
                                Телефон
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    autocomplete="tel"
                                    placeholder="+7 999 123-45-67"
                                    maxlength="30"
                                    pattern="[+0-9() .\-]{7,30}"
                                />
                            </label>
                        </div>
                    </div>
                </section>
                <section class="form-card">
                    <h2>
                        <span class="step-number">02</span>
                        Получение заказа
                    </h2>
                    <div class="delivery-options">
                        <button
                            type="button"
                            class="delivery-option"
                            :class="{active: form.delivery_method === 'delivery'}"
                            :aria-pressed="form.delivery_method === 'delivery'"
                            @click="form.delivery_method = 'delivery'"
                        >
                            <strong>Доставка</strong>
                            <small>
                                {{ money(settings?.delivery_price) }}, от
                                {{ money(settings?.free_delivery_from) }} бесплатно
                            </small>
                        </button>
                        <button
                            type="button"
                            class="delivery-option"
                            :class="{active: form.delivery_method === 'pickup'}"
                            :aria-pressed="form.delivery_method === 'pickup'"
                            @click="form.delivery_method = 'pickup'"
                        >
                            <strong>Самовывоз</strong>
                            <small>Бесплатно, демо-пункт выдачи</small>
                        </button>
                    </div>
                    <label
                        v-if="form.delivery_method === 'delivery'"
                        class="field-label"
                    >
                        Адрес доставки
                        <textarea
                            v-model="form.address"
                            required
                            autocomplete="street-address"
                            rows="2"
                            maxlength="500"
                            placeholder="Город, улица, дом, квартира"
                        ></textarea>
                    </label>
                    <p
                        v-else
                        class="muted"
                    >
                        В демо-режиме посещать пункт выдачи не нужно.
                    </p>
                    <label
                        class="field-label"
                        style="margin-top: 20px"
                    >
                        Комментарий к заказу
                        <textarea
                            v-model="form.note"
                            rows="2"
                            maxlength="1000"
                            placeholder="Если нужно что-то уточнить"
                        ></textarea>
                    </label>
                </section>
                <section class="form-card">
                    <h2>
                        <span class="step-number">03</span>
                        Демо-оплата
                    </h2>
                    <p class="muted">
                        После подтверждения создадим учебный заказ. Банковская карта не нужна, деньги не
                        списываются и товары не доставляются.
                    </p>
                    <p
                        class="muted"
                        style="font-size: 11px; margin-bottom: 0"
                    >
                        Можно использовать вымышленные контактные данные.
                    </p>
                </section>
            </div>
            <aside class="order-summary">
                <h2>Всё готово к старту</h2>
                <div
                    v-for="line in cart.lines"
                    :key="line.id"
                    class="summary-row"
                >
                    <span>{{ line.product?.name || 'Недоступный товар' }} × {{ line.quantity }}</span>
                    <span>{{ money((line.product?.price || 0) * line.quantity) }}</span>
                </div>
                <div class="summary-row">
                    <span>Доставка</span>
                    <span>{{ shipping ? money(shipping) : 'Бесплатно' }}</span>
                </div>
                <div class="summary-row total">
                    <span>Итого</span>
                    <span>{{ money(total) }}</span>
                </div>
                <p
                    v-if="error"
                    role="alert"
                    class="alert error"
                >
                    {{ error }}
                </p>
                <p
                    v-if="!cart.valid"
                    class="alert error"
                >
                    Остатки изменились.
                    <RouterLink
                        to="/cart"
                        class="text-link"
                    >
                        Обновить корзину
                    </RouterLink>
                </p>
                <button
                    type="submit"
                    class="button primary full"
                    :disabled="busy || !cart.valid || !settings"
                >
                    <span
                        v-if="busy"
                        class="spinner"
                    ></span>
                    {{ busy ? 'Оформляем…' : 'Оформить демо-заказ' }}
                    <ArrowRight
                        v-if="!busy"
                        :size="18"
                    />
                </button>
                <p class="fine-print">
                    <LockKeyhole
                        :size="12"
                        style="display: inline; margin-right: 5px"
                    />
                    Цены и наличие проверяются на сервере. Реального платежа не будет.
                </p>
            </aside>
        </form>
    </main>
</template>
