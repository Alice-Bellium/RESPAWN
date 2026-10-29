<script setup>
import {onMounted, onBeforeUnmount, ref, watch} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import {Search, SlidersHorizontal, X} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import {money} from '../lib/format';
import ProductCard from '../components/ProductCard.vue';
import Pagination from '../components/Pagination.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';

const route = useRoute();
const router = useRouter();
const categories = ref([]);
const products = ref([]);
const meta = ref({});
const loading = ref(true);
const error = ref('');
const search = ref(String(route.query.q || ''));
const filtersOpen = ref(false);
let timer;
let controller;

function update(values) {
    const query = {...route.query, ...values};
    Object.keys(query).forEach((key) => {
        if (query[key] === '' || query[key] == null || query[key] === false) delete query[key];
    });
    router.replace({query});
}
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        if (value !== String(route.query.q || '')) update({q: value, page: null});
    }, 300);
});
async function load() {
    controller?.abort();
    controller = new AbortController();
    const signal = controller.signal;
    loading.value = true;
    error.value = '';
    search.value = String(route.query.q || '');
    try {
        const params = Object.fromEntries(
            Object.entries(route.query).filter(([key]) =>
                ['q', 'category', 'sort', 'page', 'in_stock', 'max_price'].includes(key),
            ),
        );
        const {data} = await api.get('/products', {params, signal});
        products.value = data.data;
        meta.value = data.meta;
    } catch (e) {
        if (!signal.aborted) error.value = errorMessage(e);
    } finally {
        if (!signal.aborted) loading.value = false;
    }
}
watch(() => route.query, load);
onMounted(async () => {
    load();
    try {
        categories.value = (await api.get('/categories')).data.data;
    } catch {
        /* Main request has its own error state. */
    }
});
onBeforeUnmount(() => {
    clearTimeout(timer);
    controller?.abort();
});
</script>

<template>
    <main class="container page">
        <div class="breadcrumb">
            <RouterLink to="/">Главная</RouterLink>
            <span>/</span>
            <span>Каталог</span>
        </div>
        <div class="page-heading">
            <div>
                <span class="eyebrow accent">ВРЕМЯ ДЛЯ АПГРЕЙДА</span>
                <h1>ТВОЯ ЭКИПИРОВКА</h1>
            </div>
            <span class="muted">{{ meta.total || 0 }} товаров</span>
        </div>
        <div class="catalog-toolbar">
            <label class="search-field">
                <Search :size="20" />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Найти свой следующий апгрейд"
                    aria-label="Поиск товаров"
                    maxlength="120"
                />
            </label>
            <select
                :value="route.query.sort || 'newest'"
                aria-label="Сортировка"
                @change="update({sort: $event.target.value, page: null})"
            >
                <option value="newest">Сначала новые</option>
                <option value="price_asc">Сначала дешевле</option>
                <option value="price_desc">Сначала дороже</option>
            </select>
            <button
                class="button secondary filter-toggle"
                @click="filtersOpen = !filtersOpen"
            >
                <SlidersHorizontal :size="18" />
                Фильтры
            </button>
        </div>
        <div class="catalog-layout">
            <aside
                class="catalog-filters"
                :class="{open: filtersOpen}"
            >
                <div class="filter-heading">
                    <h3>Категории</h3>
                    <SlidersHorizontal :size="17" />
                </div>
                <button
                    class="category-filter"
                    :class="{active: !route.query.category}"
                    @click="update({category: null, page: null})"
                >
                    Все товары
                    <span>{{ categories.reduce((sum, category) => sum + category.products_count, 0) }}</span>
                </button>
                <button
                    v-for="category in categories"
                    :key="category.id"
                    class="category-filter"
                    :class="{active: route.query.category === category.slug}"
                    @click="update({category: category.slug, page: null})"
                >
                    {{ category.name }}
                    <span>{{ category.products_count }}</span>
                </button>
                <div class="filter-block">
                    <h3>Стоимость</h3>
                    <label class="field-label">
                        Не дороже
                        <select
                            :value="route.query.max_price || ''"
                            @change="update({max_price: $event.target.value, page: null})"
                        >
                            <option value="">Любая цена</option>
                            <option
                                v-for="value in [500000, 1000000, 2500000, 10000000]"
                                :key="value"
                                :value="value"
                            >
                                {{ money(value) }}
                            </option>
                        </select>
                    </label>
                </div>
                <label class="check-row">
                    <input
                        type="checkbox"
                        :checked="route.query.in_stock === '1'"
                        @change="update({in_stock: $event.target.checked ? '1' : null, page: null})"
                    />
                    Только в наличии
                </label>
                <button
                    v-if="Object.keys(route.query).length"
                    class="text-link reset-filter"
                    @click="router.replace({query: {}})"
                >
                    <X :size="16" />
                    Сбросить фильтры
                </button>
                <div class="filter-promo">
                    <span class="eyebrow">PLAY YOUR WAY</span>
                    <h3>
                        Новый сетап.
                        <br />
                        Новые возможности.
                    </h3>
                    <img
                        src="/images/products/mouse.svg"
                        alt=""
                        loading="lazy"
                    />
                </div>
            </aside>
            <div class="catalog-results">
                <LoadingState v-if="loading" />
                <div
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
                </div>
                <EmptyState
                    v-else-if="!products.length"
                    title="Пока ничего не нашли"
                    text="Попробуй другое название или убери часть фильтров."
                >
                    <button
                        class="button primary"
                        @click="router.replace({query: {}})"
                    >
                        Сбросить фильтры
                    </button>
                </EmptyState>
                <div
                    v-else
                    class="product-grid catalog-grid"
                >
                    <ProductCard
                        v-for="product in products"
                        :key="product.id"
                        :product="product"
                    />
                </div>
                <Pagination
                    v-if="!loading"
                    :page="meta.current_page"
                    :last="meta.last_page"
                    @change="update({page: $event})"
                />
            </div>
        </div>
    </main>
</template>
