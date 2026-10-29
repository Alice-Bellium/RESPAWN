<script setup>
import {ref, watch} from 'vue';
import {api, errorMessage} from '../lib/api';
import {useFavoritesStore} from '../stores/favorites';
import ProductCard from '../components/ProductCard.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';
const favorites = useFavoritesStore();
const products = ref([]);
const loading = ref(false);
const error = ref('');
let version = 0;
watch(
    () => [...favorites.ids],
    async (ids) => {
        const current = ++version;
        error.value = '';
        if (!ids.length) {
            products.value = [];
            loading.value = false;
            return;
        }
        loading.value = true;
        try {
            const {data} = await api.get('/products', {params: {ids, per_page: 50}});
            if (current === version) products.value = data.data;
        } catch (e) {
            if (current === version) error.value = errorMessage(e);
        } finally {
            if (current === version) loading.value = false;
        }
    },
    {immediate: true},
);
</script>
<template>
    <main class="container page">
        <div class="page-heading">
            <div>
                <span class="eyebrow accent">СОХРАНИ НА ПОТОМ</span>
                <h1>ИЗБРАННОЕ</h1>
            </div>
        </div>
        <LoadingState v-if="loading" />
        <p
            v-else-if="error"
            class="alert error"
        >
            {{ error }}
        </p>
        <div
            v-else-if="products.length"
            class="product-grid"
        >
            <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
            />
        </div>
        <EmptyState
            v-else
            title="Здесь будет твой вишлист"
            text="Нажми на сердечко у понравившегося товара."
        >
            <RouterLink
                to="/catalog"
                class="button primary"
            >
                Найти свой апгрейд
            </RouterLink>
        </EmptyState>
    </main>
</template>
