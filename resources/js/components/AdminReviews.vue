<script setup>
import {onMounted, onBeforeUnmount, ref} from 'vue';
import {api, errorMessage} from '../lib/api';
import {date} from '../lib/format';
import {useUiStore} from '../stores/ui';
import RatingStars from './RatingStars.vue';
import Pagination from './Pagination.vue';
import LoadingState from './LoadingState.vue';
import EmptyState from './EmptyState.vue';
import Modal from './Modal.vue';

const ui = useUiStore();
const reviews = ref([]);
const meta = ref({});
const status = ref('pending');
const loading = ref(false);
const busy = ref(false);
const error = ref('');
const modalError = ref('');
const selected = ref(null);
const reason = ref('');
const labels = {pending: 'На проверке', approved: 'Опубликован', rejected: 'Отклонён'};
let controller;
const lifetime = new AbortController();

async function load(page = 1) {
    controller?.abort();
    const current = new AbortController();
    controller = current;
    loading.value = true;
    error.value = '';
    try {
        const {data} = await api.get('/admin/reviews', {
            params: {page, status: status.value},
            signal: current.signal,
        });
        reviews.value = data.data;
        meta.value = data.meta;
        if (!reviews.value.length && page > 1) await load(1);
    } catch (e) {
        if (!current.signal.aborted) error.value = errorMessage(e);
    } finally {
        if (!current.signal.aborted) loading.value = false;
    }
}

function reject(review) {
    selected.value = review;
    reason.value = '';
    modalError.value = '';
}

async function moderate(review, decision) {
    if (busy.value) return;
    busy.value = true;
    error.value = '';
    modalError.value = '';
    try {
        await api.patch(
            '/admin/reviews/' + review.id,
            {
                status: decision,
                revision: review.revision,
                moderation_note: decision === 'rejected' ? reason.value : null,
            },
            {signal: lifetime.signal},
        );
        selected.value = null;
        ui.notify(decision === 'approved' ? 'Отзыв опубликован' : 'Отзыв отклонён. Автор увидит причину.');
        await load(meta.value.current_page || 1);
    } catch (e) {
        if (!lifetime.signal.aborted) {
            if (selected.value) modalError.value = errorMessage(e);
            else error.value = errorMessage(e);
        }
    } finally {
        busy.value = false;
    }
}

onMounted(() => load());
onBeforeUnmount(() => {
    controller?.abort();
    lifetime.abort();
});
</script>

<template>
    <section
        class="admin-reviews"
        aria-label="Модерация отзывов"
    >
        <div class="catalog-toolbar">
            <label class="field-label">
                Статус отзыва
                <select
                    v-model="status"
                    :disabled="busy"
                    @change="load()"
                >
                    <option value="">Все отзывы</option>
                    <option
                        v-for="(label, value) in labels"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </label>
            <button
                class="button secondary compact"
                :disabled="busy || loading"
                @click="load()"
            >
                Обновить список
            </button>
        </div>
        <p class="fine-print">
            Проверяй содержание, а не оценку. Отрицательный отзыв о товаре тоже может быть опубликован.
        </p>
        <p
            v-if="error"
            class="alert error"
            role="alert"
        >
            {{ error }}
        </p>
        <LoadingState v-if="loading" />
        <template v-else>
            <EmptyState
                v-if="!reviews.length"
                title="Таких отзывов пока нет"
                text="Отзывы покупателей появятся здесь после отправки."
            />
            <article
                v-for="review in reviews"
                :key="review.id"
                class="review-card"
            >
                <div class="review-card-heading">
                    <div>
                        <RouterLink
                            v-if="review.product.is_active"
                            class="text-link accent"
                            :to="'/product/' + review.product.slug + '#reviews'"
                        >
                            {{ review.product.name }}
                        </RouterLink>
                        <strong v-else>{{ review.product.name }} (товар скрыт)</strong>
                        <p class="muted">{{ review.author }} · {{ date(review.created_at) }}</p>
                    </div>
                    <span
                        class="review-status"
                        :class="review.status"
                    >
                        {{ labels[review.status] }}
                    </span>
                </div>
                <RatingStars :rating="review.rating" />
                <p class="review-body">{{ review.body }}</p>
                <p
                    v-if="review.moderation_note"
                    class="alert info"
                >
                    Причина: {{ review.moderation_note }}
                </p>
                <div class="review-actions">
                    <button
                        v-if="review.status !== 'approved'"
                        class="button primary compact"
                        :disabled="busy"
                        :aria-label="'Опубликовать отзыв ' + review.author"
                        @click="moderate(review, 'approved')"
                    >
                        Опубликовать
                    </button>
                    <button
                        v-if="review.status !== 'rejected'"
                        class="button secondary compact"
                        :disabled="busy"
                        :aria-label="'Отклонить отзыв ' + review.author"
                        @click="reject(review)"
                    >
                        Отклонить
                    </button>
                </div>
            </article>
            <Pagination
                :page="meta.current_page"
                :last="meta.last_page"
                label="Страницы модерации отзывов"
                @change="load"
            />
        </template>
        <Modal
            v-if="selected"
            title="Отклонить отзыв"
            @close="!busy && (selected = null)"
        >
            <p>
                <strong>{{ selected.product.name }}</strong>
            </p>
            <p class="review-body">{{ selected.body }}</p>
            <form
                class="form-stack"
                @submit.prevent="moderate(selected, 'rejected')"
            >
                <label class="field-label">
                    Причина для автора
                    <textarea
                        v-model="reason"
                        rows="3"
                        maxlength="500"
                        required
                        :disabled="busy"
                        placeholder="Объясни, что нужно исправить в отзыве"
                    ></textarea>
                </label>
                <p class="fine-print">
                    Отзыв исчезнет из публичного списка и перестанет влиять на рейтинг. Автор сможет его
                    исправить.
                </p>
                <p
                    v-if="modalError"
                    class="alert error"
                    role="alert"
                >
                    {{ modalError }}
                </p>
                <button
                    class="button primary"
                    :disabled="busy"
                >
                    {{ busy ? 'Сохраняем…' : 'Отклонить отзыв' }}
                </button>
            </form>
        </Modal>
    </section>
</template>
