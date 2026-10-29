import {createRouter, createWebHistory} from 'vue-router';
import {useAuthStore} from '../stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: () => import('../pages/HomePage.vue'),
            meta: {title: 'Твой сетап. Твои правила.'},
        },
        {
            path: '/catalog',
            name: 'catalog',
            component: () => import('../pages/CatalogPage.vue'),
            meta: {title: 'Каталог'},
        },
        {
            path: '/product/:slug',
            name: 'product',
            component: () => import('../pages/ProductPage.vue'),
            meta: {title: 'Оборудование'},
        },
        {
            path: '/cart',
            name: 'cart',
            component: () => import('../pages/CartPage.vue'),
            meta: {title: 'Корзина'},
        },
        {
            path: '/favorites',
            name: 'favorites',
            component: () => import('../pages/FavoritesPage.vue'),
            meta: {title: 'Избранное'},
        },
        {
            path: '/checkout',
            name: 'checkout',
            component: () => import('../pages/CheckoutPage.vue'),
            meta: {auth: true, title: 'Оформление заказа'},
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('../pages/AuthPage.vue'),
            meta: {guest: true, title: 'Вход'},
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('../pages/AuthPage.vue'),
            meta: {guest: true, title: 'Регистрация'},
        },
        {
            path: '/account',
            name: 'account',
            component: () => import('../pages/AccountPage.vue'),
            meta: {auth: true, title: 'Личный кабинет'},
        },
        {
            path: '/orders/:number',
            name: 'order',
            component: () => import('../pages/OrderPage.vue'),
            meta: {auth: true, title: 'Мой заказ'},
        },
        {
            path: '/admin',
            name: 'admin',
            component: () => import('../pages/AdminPage.vue'),
            meta: {auth: true, admin: true, title: 'Управление магазином'},
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'not-found',
            component: () => import('../pages/NotFoundPage.vue'),
            meta: {title: 'Страница не найдена'},
        },
    ],
    scrollBehavior(to, from, saved) {
        if (saved) return saved;
        if (to.hash) return {el: to.hash, top: 100, behavior: 'smooth'};
        if (to.path === from.path && to.name === 'catalog') return false;
        return {top: 0};
    },
});

router.beforeEach(async (to) => {
    if (to.meta.auth || to.meta.guest) {
        const auth = useAuthStore();
        await auth.init();
        if (to.meta.auth && !auth.user) return {name: 'login', query: {redirect: to.fullPath}};
        if (to.meta.admin && !auth.user?.is_admin) return {name: 'account'};
        if (to.meta.guest && auth.user) return {name: 'account'};
    }
});

router.afterEach((to) => {
    document.title = to.meta.title + ' | RESPAWN';
});
export default router;
