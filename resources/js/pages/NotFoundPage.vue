<script setup>
import EmptyState from '../components/EmptyState.vue';
</script>

<template>
    <main class="container page">
        <EmptyState
            title="Похоже, это другой уровень"
            text="Такой страницы нет. Вернёмся за новым оборудованием?"
        >
            <RouterLink
                to="/catalog"
                class="button primary"
            >
                В каталог
            </RouterLink>
        </EmptyState>
    </main>
</template>
