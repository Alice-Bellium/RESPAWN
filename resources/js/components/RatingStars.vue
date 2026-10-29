<script setup>
import {computed} from 'vue';
import {Star} from '@lucide/vue';

const props = defineProps({
    rating: {type: Number, default: null},
    count: {type: Number, default: null},
});
const label = computed(() =>
    props.rating === null
        ? 'Пока нет отзывов'
        : `Оценка ${props.rating.toFixed(1).replace('.', ',')} из 5${props.count === null ? '' : ', отзывов: ' + props.count}`,
);
</script>

<template>
    <span
        class="rating-summary"
        role="img"
        :aria-label="label"
    >
        <span
            class="rating-stars"
            aria-hidden="true"
        >
            <span
                v-for="star in 5"
                :key="star"
                class="rating-star"
            >
                <Star :size="16" />
                <span
                    class="rating-star-fill"
                    :style="{width: Math.max(0, Math.min(1, (rating || 0) - star + 1)) * 100 + '%'}"
                >
                    <Star :size="16" />
                </span>
            </span>
        </span>
        <span
            v-if="rating !== null"
            aria-hidden="true"
        >
            {{ rating.toFixed(1).replace('.', ',') }}
            <span
                v-if="count !== null"
                class="muted"
            >
                ({{ count }})
            </span>
        </span>
        <span
            v-else
            class="muted"
            aria-hidden="true"
        >
            Пока нет отзывов
        </span>
    </span>
</template>
