<script setup>
import {ChevronLeft, ChevronRight} from '@lucide/vue';
defineProps({page: Number, last: Number});
defineEmits(['change']);
</script>

<template>
    <nav
        v-if="last > 1"
        class="pagination"
        aria-label="Страницы каталога"
    >
        <button
            class="button secondary compact"
            :disabled="page <= 1"
            aria-label="Предыдущая страница"
            @click="$emit('change', page - 1)"
        >
            <ChevronLeft :size="18" />
        </button>
        <span>
            Страница
            <b>{{ page }}</b>
            из {{ last }}
        </span>
        <button
            class="button secondary compact"
            :disabled="page >= last"
            aria-label="Следующая страница"
            @click="$emit('change', page + 1)"
        >
            <ChevronRight :size="18" />
        </button>
    </nav>
</template>
