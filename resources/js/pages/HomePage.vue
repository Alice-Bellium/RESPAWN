<script setup>
import {onMounted, ref} from 'vue';
import {ArrowUpRight, ArrowRight, Zap, PackageCheck, ShieldCheck, Cpu} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import ProductCard from '../components/ProductCard.vue';
import LoadingState from '../components/LoadingState.vue';

const products = ref([]);
const loading = ref(true);
const error = ref('');
const categories = [
    {slug: 'keyboards', name: 'Клавиатуры', sub: 'Каждое нажатие решает', kind: 'keyboard', index: '01'},
    {slug: 'mice', name: 'Мыши', sub: 'Точность без компромиссов', kind: 'mouse', index: '02'},
    {slug: 'chairs', name: 'Кресла', sub: 'Твоя зона комфорта', kind: 'chair', index: '03'},
    {slug: 'computers', name: 'Компьютеры', sub: 'Больше мощности. Больше FPS.', kind: 'pc', index: '04'},
];
onMounted(async () => {
    try {
        products.value = (await api.get('/products', {params: {featured: 1, per_page: 4}})).data.data;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <main>
        <section class="hero container">
            <div class="hero-copy">
                <span class="eyebrow accent">
                    <span class="status-dot"></span>
                    GEAR UP. GAME ON.
                </span>
                <h1>
                    ТВОЙ СЕТАП.
                    <br />
                    ТВОИ
                    <span>ПРАВИЛА.</span>
                </h1>
                <p>
                    Собери пространство, в которое хочется возвращаться. Железо, периферия и характер для
                    твоей игры.
                </p>
                <div class="hero-actions">
                    <RouterLink
                        to="/catalog"
                        class="button primary"
                    >
                        Собрать свой сетап
                        <ArrowUpRight :size="21" />
                    </RouterLink>
                    <a
                        href="#categories"
                        class="text-link"
                    >
                        Выбрать категорию
                        <ArrowRight :size="17" />
                    </a>
                </div>
                <div class="hero-meta">
                    <div>
                        <strong>04</strong>
                        <span>
                            категории
                            <br />
                            для твоего сетапа
                        </span>
                    </div>
                    <span class="vertical-rule"></span>
                    <div>
                        <Zap :size="28" />
                        <span>
                            От первого клика
                            <br />
                            до полной сборки
                        </span>
                    </div>
                </div>
            </div>
            <div
                class="hero-scene"
                aria-label="Игровой компьютер, клавиатура и мышь RESPAWN"
            >
                <div class="hero-orbit"></div>
                <div class="hero-orbit orbit-two"></div>
                <span class="scene-caption">RESPAWN / NEXT LEVEL EQUIPMENT</span>
                <div class="hero-glow"></div>
                <img
                    class="hero-pc"
                    src="/images/products/pc.svg"
                    alt="Игровой компьютер с подсветкой"
                    fetchpriority="high"
                />
                <img
                    class="hero-keyboard"
                    src="/images/products/keyboard.svg"
                    alt=""
                    width="640"
                    height="500"
                />
                <img
                    class="hero-mouse"
                    src="/images/products/mouse.svg"
                    alt=""
                    width="640"
                    height="500"
                />
                <div class="scene-label">
                    <span class="status-dot"></span>
                    СЕТАП ГОТОВ К ИГРЕ
                    <ArrowUpRight :size="18" />
                </div>
                <span class="scene-coordinate">59.9386° N / PLAYER ONE</span>
                <span class="scene-plus plus-one">+</span>
                <span class="scene-plus plus-two">+</span>
            </div>
        </section>
        <div
            class="ticker"
            aria-hidden="true"
        >
            <div>
                RESPAWN YOUR SETUP
                <span>✳</span>
                PLAY YOUR WAY
                <span>✳</span>
                BUILD. PLAY. REPEAT.
                <span>✳</span>
                RESPAWN YOUR SETUP
                <span>✳</span>
                PLAY YOUR WAY
                <span>✳</span>
                BUILD. PLAY. REPEAT.
                <span>✳</span>
            </div>
        </div>
        <section
            id="categories"
            class="section container"
        >
            <div class="section-heading">
                <div>
                    <span class="eyebrow muted">ВЫБЕРИ СВОЙ АПГРЕЙД</span>
                    <h2>Всё начинается с деталей</h2>
                </div>
                <RouterLink
                    to="/catalog"
                    class="text-link"
                >
                    Весь каталог
                    <ArrowUpRight :size="18" />
                </RouterLink>
            </div>
            <div class="category-grid">
                <RouterLink
                    v-for="category in categories"
                    :key="category.slug"
                    :to="'/catalog?category=' + category.slug"
                    class="category-card"
                >
                    <div class="category-top">
                        <span>{{ category.index }} /</span>
                        <ArrowUpRight :size="23" />
                    </div>
                    <img
                        :src="'/images/products/' + category.kind + '.svg'"
                        :alt="category.name"
                        loading="lazy"
                        width="640"
                        height="500"
                    />
                    <div>
                        <h3>{{ category.name }}</h3>
                        <p>{{ category.sub }}</p>
                    </div>
                </RouterLink>
            </div>
        </section>
        <section class="section container">
            <div class="section-heading">
                <div>
                    <span class="eyebrow accent">PLAYER'S CHOICE</span>
                    <h2>В центре внимания</h2>
                </div>
                <RouterLink
                    to="/catalog?sort=newest"
                    class="text-link"
                >
                    Больше оборудования
                    <ArrowUpRight :size="18" />
                </RouterLink>
            </div>
            <LoadingState v-if="loading" />
            <p
                v-else-if="error"
                class="alert error"
            >
                {{ error }}
            </p>
            <div
                v-else
                class="product-grid"
            >
                <ProductCard
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                />
            </div>
        </section>
        <section class="container">
            <div class="setup-banner">
                <div>
                    <span class="eyebrow">НЕ ПРОСТО РАБОЧЕЕ МЕСТО</span>
                    <h2>
                        Твоя территория.
                        <br />
                        Твой следующий уровень.
                    </h2>
                    <p>
                        От тихой механики до мощного игрового ПК.
                        <br />
                        Подбери оборудование под свой ритм.
                    </p>
                    <RouterLink
                        to="/catalog?category=computers"
                        class="button dark"
                    >
                        Выбрать компьютер
                        <ArrowUpRight :size="20" />
                    </RouterLink>
                </div>
                <span
                    class="banner-symbol"
                    aria-hidden="true"
                >
                    R
                </span>
                <Cpu
                    class="banner-cpu"
                    :size="130"
                    :stroke-width="1"
                />
            </div>
        </section>
        <section
            id="about"
            class="section container about-section"
        >
            <div>
                <span class="eyebrow muted">ЗА КАЖДЫМ СЕТАПОМ СТОИТ ЧЕЛОВЕК</span>
                <h2>
                    Создано для тех,
                    <br />
                    кто любит детали.
                </h2>
                <p>
                    RESPAWN объединяет всё для игрового пространства в одном месте. Выбирай, сравнивай
                    характеристики и собирай комплект, который подходит именно тебе.
                </p>
                <p class="muted">
                    Это учебный full-stack проект. Можно пройти весь путь покупки и посмотреть управление
                    магазином. Оплата имитируется, реальные заказы не отправляются.
                </p>
            </div>
            <div class="feature-list">
                <article>
                    <Zap />
                    <div>
                        <h3>Всё в одном сетапе</h3>
                        <p>Периферия, компьютеры и кресла с подробными характеристиками.</p>
                    </div>
                </article>
                <article>
                    <PackageCheck />
                    <div>
                        <h3>Понятный путь заказа</h3>
                        <p>Остатки, стоимость доставки и статусы в личном кабинете.</p>
                    </div>
                </article>
                <article>
                    <ShieldCheck />
                    <div>
                        <h3>Можно попробовать</h3>
                        <p>Демо-оформление без банковской карты и списаний.</p>
                    </div>
                </article>
            </div>
        </section>
    </main>
</template>
