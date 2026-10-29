<script setup>
import {onMounted, ref} from 'vue';
import {Minus, Plus, Trash2, ArrowRight} from '@lucide/vue';
import {useCartStore} from '../stores/cart';
import {errorMessage} from '../lib/api';
import {money} from '../lib/format';
import ProductVisual from '../components/ProductVisual.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
const cart = useCartStore();
const error = ref('');
async function load() {
    error.value = '';
    try {
        await cart.refresh();
    } catch (e) {
        error.value = errorMessage(e);
    }
}
onMounted(load);
</script>

<template>
    <main class="container page">
        <div class="breadcrumb">
            <RouterLink to="/catalog">Каталог</RouterLink>
            <span>/</span>
            <span>Корзина</span>
        </div>
        <div class="page-heading">
            <div>
                <span class="eyebrow accent">ТВОЙ СЛЕДУЮЩИЙ АПГРЕЙД</span>
                <h1>КОРЗИНА</h1>
            </div>
            <span class="muted">Товаров: {{ cart.count }}</span>
        </div>
        <LoadingState v-if="cart.loading" />
        <p
            v-else-if="error"
            class="alert error"
        >
            {{ error }}
            <button
                class="text-link"
                @click="load"
            >
                Повторить
            </button>
        </p>
        <EmptyState
            v-else-if="!cart.items.length"
            title="Сетап ещё не собран"
            text="Добавь что-нибудь из каталога. Твой следующий уровень уже близко."
        >
            <RouterLink
                to="/catalog"
                class="button primary"
            >
                Перейти в каталог
                <ArrowRight :size="18" />
            </RouterLink>
        </EmptyState>
        <div
            v-else
            class="cart-layout"
        >
            <div>
                <article
                    v-for="line in cart.lines"
                    :key="line.id"
                    class="cart-line"
                >
                    <RouterLink
                        v-if="line.product"
                        :to="'/product/' + line.product.slug"
                        class="cart-line-art"
                    >
                        <ProductVisual
                            :kind="line.product.kind"
                            :name="line.product.name"
                        />
                    </RouterLink>
                    <div v-else></div>
                    <div>
                        <h3>
                            <RouterLink
                                v-if="line.product"
                                :to="'/product/' + line.product.slug"
                            >
                                {{ line.product.name }}
                            </RouterLink>
                            <span v-else>Товар больше недоступен</span>
                        </h3>
                        <p>{{ line.product?.category?.name || 'Удалите эту позицию перед оформлением' }}</p>
                        <div class="line-controls">
                            <div
                                v-if="line.product"
                                class="quantity"
                            >
                                <button
                                    :disabled="line.quantity <= 1"
                                    aria-label="Уменьшить количество"
                                    @click="cart.setQuantity(line.id, line.quantity - 1)"
                                >
                                    <Minus :size="14" />
                                </button>
                                <span>{{ line.quantity }}</span>
                                <button
                                    :disabled="line.quantity >= Math.min(20, line.product.stock)"
                                    aria-label="Увеличить количество"
                                    @click="cart.setQuantity(line.id, line.quantity + 1)"
                                >
                                    <Plus :size="14" />
                                </button>
                            </div>
                            <button
                                class="icon-button"
                                :aria-label="'Удалить ' + (line.product?.name || 'товар')"
                                @click="cart.remove(line.id)"
                            >
                                <Trash2 :size="17" />
                            </button>
                        </div>
                        <p
                            v-if="line.product && line.quantity > line.product.stock"
                            class="accent"
                        >
                            Доступно {{ line.product.stock }} шт. Уменьшите количество.
                        </p>
                    </div>
                    <strong
                        v-if="line.product"
                        class="line-price"
                    >
                        {{ money(line.product.price * line.quantity) }}
                    </strong>
                </article>
                <RouterLink
                    to="/catalog"
                    class="text-link"
                    style="margin-top: 25px"
                >
                    Продолжить покупки
                    <ArrowRight :size="17" />
                </RouterLink>
            </div>
            <aside class="order-summary">
                <h2>Твой заказ</h2>
                <div class="summary-row">
                    <span>Товаров</span>
                    <span>{{ cart.count }}</span>
                </div>
                <div class="summary-row">
                    <span>Доставка</span>
                    <span>При оформлении</span>
                </div>
                <div class="summary-row total">
                    <span>Итого</span>
                    <span>{{ money(cart.subtotal) }}</span>
                </div>
                <RouterLink
                    v-if="cart.valid"
                    to="/checkout"
                    class="button primary full"
                >
                    К оформлению
                    <ArrowRight :size="18" />
                </RouterLink>
                <p
                    v-else
                    class="alert error"
                >
                    Обнови состав корзины, чтобы продолжить.
                </p>
                <p class="fine-print">
                    Демонстрационный магазин. Реальные деньги не списываются. Окончательная стоимость
                    проверяется сервером при оформлении.
                </p>
            </aside>
        </div>
    </main>
</template>
