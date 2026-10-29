<script setup>
import {onMounted, ref} from 'vue';
import {useRoute} from 'vue-router';
import {CheckCircle2} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import {date, money, statusLabels} from '../lib/format';
import ProductVisual from '../components/ProductVisual.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
import Modal from '../components/Modal.vue';
const route = useRoute();
const order = ref(null);
const loading = ref(true);
const error = ref('');
const busy = ref(false);
const confirm = ref(false);
onMounted(async () => {
    try {
        order.value = (await api.get('/orders/' + route.params.number)).data.data;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
});
async function cancel() {
    busy.value = true;
    error.value = '';
    try {
        order.value = (await api.post('/orders/' + order.value.number + '/cancel')).data.data;
        confirm.value = false;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        busy.value = false;
    }
}
</script>
<template>
    <main class="container page">
        <div class="breadcrumb">
            <RouterLink to="/account">Мои заказы</RouterLink>
            <span>/</span>
            <span>Детали заказа</span>
        </div>
        <LoadingState v-if="loading" />
        <EmptyState
            v-else-if="!order"
            title="Не удалось открыть заказ"
            :text="error"
        >
            <RouterLink
                to="/account"
                class="button primary"
            >
                В личный кабинет
            </RouterLink>
        </EmptyState>
        <template v-else>
            <div
                v-if="route.query.placed && order.status !== 'cancelled'"
                class="order-success"
            >
                <CheckCircle2 :size="40" />
                <div>
                    <h2>Отличный выбор. Заказ оформлен!</h2>
                    <p>Это демо-заказ. Деньги не списаны, отправка не выполняется.</p>
                </div>
            </div>
            <div class="page-heading">
                <div>
                    <span class="eyebrow accent">ЗАКАЗ ОТ {{ date(order.created_at) }}</span>
                    <h1 style="font-family: Manrope, sans-serif; font-size: 32px">Твой новый сетап</h1>
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
                v-if="error && !confirm"
                class="alert error"
            >
                {{ error }}
            </p>
            <div class="cart-layout">
                <div>
                    <article
                        v-for="item in order.items"
                        :key="item.id"
                        class="cart-line"
                    >
                        <div class="cart-line-art">
                            <ProductVisual
                                :kind="item.kind"
                                :name="item.name"
                            />
                        </div>
                        <div>
                            <h3>{{ item.name }}</h3>
                            <p>{{ item.quantity }} шт. × {{ money(item.unit_price) }}</p>
                            <span
                                class="muted"
                                style="font-size: 10px"
                            >
                                {{ item.sku }}
                            </span>
                        </div>
                        <strong class="line-price">{{ money(item.total) }}</strong>
                    </article>
                    <div class="info-grid">
                        <div>
                            <h3>Получатель</h3>
                            <p>
                                {{ order.customer_name }}
                                <br />
                                {{ order.email }}
                                <br />
                                {{ order.phone }}
                            </p>
                        </div>
                        <div>
                            <h3>{{ order.delivery_method === 'pickup' ? 'Самовывоз' : 'Доставка' }}</h3>
                            <p>{{ order.address || 'Учебный пункт выдачи, посещение не требуется' }}</p>
                            <p v-if="order.note">{{ order.note }}</p>
                        </div>
                    </div>
                </div>
                <aside class="order-summary">
                    <h2>Сумма заказа</h2>
                    <div class="summary-row">
                        <span>Товары</span>
                        <span>{{ money(order.subtotal) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Доставка</span>
                        <span>{{ money(order.delivery_total) }}</span>
                    </div>
                    <div class="summary-row total">
                        <span>Итого</span>
                        <span>{{ money(order.total) }}</span>
                    </div>
                    <span class="tag">ДЕМО-ОПЛАТА</span>
                    <p class="fine-print">
                        Реальные деньги не списываются. Цены в этом заказе сохраняются даже при изменении
                        каталога.
                    </p>
                    <button
                        v-if="order.status === 'processing'"
                        class="button secondary full"
                        style="margin-top: 25px"
                        @click="confirm = true"
                    >
                        Отменить заказ
                    </button>
                </aside>
            </div>
        </template>
        <Modal
            v-if="confirm"
            title="Отменить заказ?"
            @close="!busy && (confirm = false)"
        >
            <p class="muted">Товары вернутся на склад. Отменённый заказ останется в истории.</p>
            <p
                v-if="error"
                class="alert error"
            >
                {{ error }}
            </p>
            <div class="hero-actions">
                <button
                    class="button primary"
                    :disabled="busy"
                    @click="cancel"
                >
                    {{ busy ? 'Отменяем…' : 'Да, отменить заказ' }}
                </button>
                <button
                    class="button secondary"
                    :disabled="busy"
                    @click="confirm = false"
                >
                    Оставить заказ
                </button>
            </div>
        </Modal>
    </main>
</template>
