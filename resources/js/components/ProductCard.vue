<script setup>
import {Heart, Plus, Check} from '@lucide/vue';
import ProductVisual from './ProductVisual.vue';
import RatingStars from './RatingStars.vue';
import {money} from '../lib/format';
import {useCartStore} from '../stores/cart';
import {useFavoritesStore} from '../stores/favorites';
import {useUiStore} from '../stores/ui';

defineProps({product: {type: Object, required: true}});
const cart = useCartStore();
const favorites = useFavoritesStore();
const ui = useUiStore();

function add(product) {
    ui.notify(cart.add(product) ? 'Товар добавлен в корзину' : 'Доступное количество уже в корзине');
}
</script>

<template>
    <article class="product-card">
        <div
            class="product-card-art"
            :class="'art-' + product.kind"
        >
            <RouterLink
                :to="'/product/' + product.slug"
                :aria-label="product.name"
            >
                <ProductVisual
                    :kind="product.kind"
                    :name="product.name"
                />
            </RouterLink>
            <span
                v-if="product.badge"
                class="tag product-tag"
            >
                {{ product.badge }}
            </span>
            <button
                class="icon-button favorite-button"
                :class="{selected: favorites.ids.includes(product.id)}"
                :aria-label="favorites.ids.includes(product.id) ? 'Убрать из избранного' : 'В избранное'"
                :aria-pressed="favorites.ids.includes(product.id)"
                @click="favorites.toggle(product.id)"
            >
                <Heart :size="19" />
            </button>
        </div>
        <div class="product-card-info">
            <p class="eyebrow muted">{{ product.category?.name }}</p>
            <RouterLink
                class="product-name"
                :to="'/product/' + product.slug"
            >
                {{ product.name }}
            </RouterLink>
            <p class="product-hint">
                {{
                    Object.values(product.specifications || {})
                        .slice(0, 2)
                        .join(' / ')
                }}
            </p>
            <RatingStars
                :rating="product.rating"
                :count="product.reviews_count"
            />
            <div class="product-card-bottom">
                <div>
                    <strong class="price">{{ money(product.price) }}</strong>
                    <small :class="product.stock ? 'stock' : 'muted'">
                        {{ product.stock ? 'В наличии' : 'Ожидаем поставку' }}
                    </small>
                </div>
                <button
                    class="add-button"
                    :disabled="!product.stock"
                    :aria-label="'В корзину: ' + product.name"
                    @click="add(product)"
                >
                    <Check
                        v-if="cart.items.some((item) => item.id === product.id)"
                        :size="22"
                    />
                    <Plus
                        v-else
                        :size="22"
                    />
                </button>
            </div>
        </div>
    </article>
</template>
