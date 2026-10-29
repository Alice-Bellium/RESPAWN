<script setup>
import {Orbit} from '@lucide/vue';
defineProps({title: String, text: String});
</script>
<template>
    <div class="empty-state">
        <Orbit :size="46" />
        <h2>{{ title }}</h2>
        <p class="muted">{{ text }}</p>
        <slot />
    </div>
</template>
