<script setup>
import {ref, watch} from 'vue';
import {useRoute} from 'vue-router';
import {Heart, ShoppingBag, ArrowRight, Truck, ShieldCheck} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import {money} from '../lib/format';
import {useCartStore} from '../stores/cart';
import {useFavoritesStore} from '../stores/favorites';
import {useUiStore} from '../stores/ui';
import ProductVisual from '../components/ProductVisual.vue';
import ProductCard from '../components/ProductCard.vue';
import RatingStars from '../components/RatingStars.vue';
import ProductReviews from '../components/ProductReviews.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';

const route = useRoute();
const product = ref(null);
const related = ref([]);
const error = ref('');
const loading = ref(true);
const cart = useCartStore();
const favorites = useFavoritesStore();
const ui = useUiStore();

watch(
    () => route.params.slug,
    async (slug, previous, onCleanup) => {
        if (!slug) return;
        const controller = new AbortController();
        const signal = controller.signal;

        onCleanup(() => controller.abort());
        loading.value = true;
        error.value = '';
        related.value = [];

        try {
            const {data} = await api.get('/products/' + encodeURIComponent(slug), {signal});
            product.value = data.data;
            document.title = product.value.name + ' | RESPAWN';
        } catch (e) {
            if (!signal.aborted)
                error.value =
                    e.response?.status === 404 ? 'Этот товар не найден или временно скрыт.' : errorMessage(e);
        } finally {
            if (!signal.aborted) loading.value = false;
        }

        if (signal.aborted || error.value) return;

        try {
            const {data} = await api.get('/products', {
                params: {category: product.value.category.slug, per_page: 5},
                signal,
            });
            related.value = data.data.filter((item) => item.id !== product.value.id).slice(0, 4);
        } catch {
            // Suggestions are optional; the product stays usable when they fail.
        }
    },
    {immediate: true},
);

function add() {
    ui.notify(cart.add(product.value) ? 'Товар добавлен в корзину' : 'Доступное количество уже в корзине');
}
</script>

<template>
    <main class="container page">
        <LoadingState v-if="loading" />
        <EmptyState
            v-else-if="error"
            title="Товар недоступен"
            :text="error"
        >
            <RouterLink
                to="/catalog"
                class="button primary"
            >
                В каталог
            </RouterLink>
        </EmptyState>
        <template v-else>
            <div class="breadcrumb">
                <RouterLink to="/catalog">Каталог</RouterLink>
                <span>/</span>
                <RouterLink :to="'/catalog?category=' + product.category.slug">
                    {{ product.category.name }}
                </RouterLink>
                <span>/</span>
                <span>{{ product.name }}</span>
            </div>
            <div class="product-detail">
                <div
                    class="detail-art"
                    :class="'art-' + product.kind"
                >
                    <span class="eyebrow">RESPAWN / {{ product.kind.toUpperCase() }}</span>
                    <ProductVisual
                        :kind="product.kind"
                        :name="product.name"
                    />
                    <span class="detail-art-caption">DESIGNED FOR YOUR NEXT LEVEL</span>
                </div>
                <div class="detail-info">
                    <span class="eyebrow accent">{{ product.category.name }}</span>
                    <h1>{{ product.name }}</h1>
                    <a
                        href="#reviews"
                        class="rating-link"
                    >
                        <RatingStars
                            :rating="product.rating"
                            :count="product.reviews_count"
                        />
                    </a>
                    <p class="muted">{{ product.description }}</p>
                    <div class="detail-stock">
                        <span :class="product.stock ? 'stock' : 'muted'">
                            {{ product.stock ? 'В наличии: ' + product.stock + ' шт.' : 'Ожидаем поставку' }}
                        </span>
                        <span class="muted">Арт. {{ product.sku }}</span>
                    </div>
                    <strong class="detail-price">{{ money(product.price) }}</strong>
                    <div class="detail-actions">
                        <button
                            class="button primary"
                            :disabled="!product.stock"
                            @click="add"
                        >
                            <ShoppingBag :size="20" />
                            {{ product.stock ? 'Добавить в корзину' : 'Нет в наличии' }}
                        </button>
                        <button
                            class="button secondary square"
                            :aria-pressed="favorites.ids.includes(product.id)"
                            aria-label="В избранное"
                            @click="favorites.toggle(product.id)"
                        >
                            <Heart :class="{'fill-heart': favorites.ids.includes(product.id)}" />
                        </button>
                    </div>
                    <RouterLink
                        v-if="cart.items.some((item) => item.id === product.id)"
                        to="/cart"
                        class="text-link accent"
                    >
                        Перейти в корзину
                        <ArrowRight :size="17" />
                    </RouterLink>
                    <div class="detail-benefit">
                        <Truck :size="20" />
                        <span>Стоимость доставки рассчитаем при оформлении</span>
                    </div>
                    <div class="detail-benefit">
                        <ShieldCheck :size="20" />
                        <span>Демо-покупка без реальных списаний</span>
                    </div>
                </div>
            </div>
            <section class="spec-section">
                <h2>Всё в деталях</h2>
                <dl class="spec-table">
                    <div
                        v-for="(value, key) in product.specifications"
                        :key="key"
                    >
                        <dt>{{ key }}</dt>
                        <dd>{{ value }}</dd>
                    </div>
                    <div>
                        <dt>Бренд</dt>
                        <dd>{{ product.brand }}</dd>
                    </div>
                </dl>
            </section>
            <ProductReviews
                :key="product.id"
                :product="product"
                @rating-change="
                    product.rating = $event.average;
                    product.reviews_count = $event.count;
                "
            />
            <section
                v-if="related.length"
                class="section"
            >
                <div class="section-heading"><h2>Ещё в этой категории</h2></div>
                <div class="product-grid">
                    <ProductCard
                        v-for="item in related"
                        :key="item.id"
                        :product="item"
                    />
                </div>
            </section>
        </template>
    </main>
</template>
