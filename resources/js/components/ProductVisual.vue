<script setup>
defineProps({kind: {type: String, default: 'keyboard'}, name: {type: String, default: ''}});
</script>

<template>
    <img
        :src="
            '/images/products/' +
            (['keyboard', 'mouse', 'chair', 'pc'].includes(kind) ? kind : 'keyboard') +
            '.svg'
        "
        :alt="name"
        class="product-visual"
        loading="lazy"
        width="640"
        height="500"
    />
</template>
