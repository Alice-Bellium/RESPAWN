<script setup>
import {ref, watch} from 'vue';
import {useRoute} from 'vue-router';
import {ShoppingBag, Heart, UserRound, Menu, X, ArrowUpRight} from '@lucide/vue';
import {useCartStore} from '../stores/cart';
import {useAuthStore} from '../stores/auth';

const cart = useCartStore();
const auth = useAuthStore();
const route = useRoute();
const open = ref(false);

watch(
    () => route.fullPath,
    () => {
        open.value = false;
    },
);
</script>

<template>
    <div class="announcement">
        <span class="status-dot"></span>
        ТВОЙ СЛЕДУЮЩИЙ УРОВЕНЬ НАЧИНАЕТСЯ ЗДЕСЬ
        <span class="announcement-right">DEMO STORE / EST. 2026</span>
    </div>
    <header class="site-header">
        <div class="container header-inner">
            <RouterLink
                to="/"
                class="logo"
                aria-label="RESPAWN, главная"
            >
                <span class="logo-symbol">R</span>
                RESPAWN
                <span class="logo-dot">®</span>
            </RouterLink>
            <nav
                class="desktop-nav"
                aria-label="Главное меню"
            >
                <RouterLink
                    to="/catalog"
                    :class="{'is-current': route.name === 'catalog' && !route.query.category}"
                >
                    Каталог
                    <ArrowUpRight :size="14" />
                </RouterLink>
                <RouterLink
                    to="/catalog?category=computers"
                    :class="{'is-current': route.query.category === 'computers'}"
                >
                    Компьютеры
                </RouterLink>
                <RouterLink
                    to="/catalog?category=keyboards"
                    :class="{'is-current': ['keyboards', 'mice'].includes(route.query.category)}"
                >
                    Периферия
                </RouterLink>
                <RouterLink
                    to="/#about"
                    :class="{'is-current': route.hash === '#about'}"
                >
                    О проекте
                </RouterLink>
            </nav>
            <div class="header-actions">
                <RouterLink
                    to="/favorites"
                    class="icon-button"
                    aria-label="Избранное"
                >
                    <Heart :size="21" />
                </RouterLink>
                <RouterLink
                    :to="auth.user ? '/account' : '/login'"
                    class="icon-button account-link"
                    aria-label="Личный кабинет"
                >
                    <UserRound :size="21" />
                </RouterLink>
                <RouterLink
                    to="/cart"
                    class="cart-link"
                    aria-label="Корзина"
                >
                    <ShoppingBag :size="20" />
                    <span class="cart-label">Корзина</span>
                    <b>{{ cart.count }}</b>
                </RouterLink>
                <button
                    class="icon-button menu-toggle"
                    :aria-expanded="open"
                    aria-label="Меню"
                    @click="open = !open"
                >
                    <X v-if="open" />
                    <Menu v-else />
                </button>
            </div>
        </div>
        <nav
            v-if="open"
            class="mobile-nav"
            aria-label="Мобильное меню"
        >
            <RouterLink
                to="/catalog"
                :class="{'is-current': route.name === 'catalog' && !route.query.category}"
            >
                Весь каталог
            </RouterLink>
            <RouterLink
                to="/catalog?category=computers"
                :class="{'is-current': route.query.category === 'computers'}"
            >
                Компьютеры
            </RouterLink>
            <RouterLink to="/account">Личный кабинет</RouterLink>
            <RouterLink
                v-if="auth.user?.is_admin"
                to="/admin"
            >
                Управление магазином
            </RouterLink>
        </nav>
    </header>
</template>
