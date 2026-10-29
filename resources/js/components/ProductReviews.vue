<script setup>
import {onMounted, onBeforeUnmount, ref, watch} from 'vue';
import {useRoute} from 'vue-router';
import {Star, BadgeCheck} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import {date} from '../lib/format';
import {useAuthStore} from '../stores/auth';
import {useUiStore} from '../stores/ui';
import RatingStars from './RatingStars.vue';
import LoadingState from './LoadingState.vue';
import Pagination from './Pagination.vue';

const props = defineProps({product: {type: Object, required: true}});
const emit = defineEmits(['rating-change']);
const auth = useAuthStore();
const ui = useUiStore();
const route = useRoute();
const reviews = ref([]);
const meta = ref({});
const summary = ref({average: props.product.rating, count: props.product.reviews_count});
const own = ref(null);
const eligible = ref(false);
const loading = ref(true);
const contextLoading = ref(false);
const busy = ref(false);
const editing = ref(false);
const listError = ref('');
const contextError = ref('');
const formError = ref('');
const conflict = ref(false);
const form = ref({rating: 0, body: ''});
const labels = {pending: 'На проверке', approved: 'Опубликован', rejected: 'Нужны исправления'};
let listController;
let ownController;
const lifetime = new AbortController();
const endpoint = '/products/' + encodeURIComponent(props.product.slug);

async function load(page = 1) {
    listController?.abort();
    const controller = new AbortController();
    listController = controller;
    loading.value = true;
    listError.value = '';
    try {
        const {data} = await api.get(endpoint + '/reviews', {
            params: {page},
            signal: controller.signal,
        });
        reviews.value = data.data;
        meta.value = data.meta;
        summary.value = data.summary;
        emit('rating-change', data.summary);
    } catch (e) {
        if (!controller.signal.aborted) listError.value = errorMessage(e);
    } finally {
        if (!controller.signal.aborted) loading.value = false;
    }
}

function edit() {
    form.value = {rating: own.value?.rating || 0, body: own.value?.body || ''};
    formError.value = '';
    conflict.value = false;
    editing.value = true;
}

async function loadOwn() {
    ownController?.abort();
    own.value = null;
    eligible.value = false;
    editing.value = false;
    contextError.value = '';
    formError.value = '';
    conflict.value = false;
    if (!auth.user) {
        contextLoading.value = false;
        return;
    }
    const controller = new AbortController();
    ownController = controller;
    contextLoading.value = true;
    try {
        const {data} = await api.get(endpoint + '/review', {signal: controller.signal});
        own.value = data.review;
        eligible.value = data.can_review;
        if (eligible.value && !own.value) edit();
    } catch (e) {
        if (!controller.signal.aborted) contextError.value = errorMessage(e);
    } finally {
        if (!controller.signal.aborted) contextLoading.value = false;
    }
}

async function save() {
    if (busy.value || conflict.value) return;
    busy.value = true;
    formError.value = '';
    try {
        const {data} = await api.put(
            endpoint + '/review',
            {
                ...form.value,
                revision: own.value?.revision || 0,
            },
            {signal: lifetime.signal},
        );
        own.value = data.data;
        editing.value = false;
        ui.notify('Отзыв сохранён. После проверки он появится на странице товара.');
        await load(1);
    } catch (e) {
        if (!lifetime.signal.aborted) {
            formError.value = errorMessage(e);
            conflict.value = e.response?.status === 409;
        }
    } finally {
        busy.value = false;
    }
}

watch(() => auth.user?.id, loadOwn, {immediate: true});
onMounted(() => {
    load();
    if (route.hash === '#reviews') document.getElementById('reviews')?.scrollIntoView();
});
onBeforeUnmount(() => {
    listController?.abort();
    ownController?.abort();
    lifetime.abort();
});
</script>

<template>
    <section
        id="reviews"
        class="reviews-section"
    >
        <div class="section-heading">
            <div>
                <span class="eyebrow accent">ОПЫТ ПОКУПАТЕЛЕЙ</span>
                <h2>Отзывы о товаре</h2>
                <RatingStars
                    :rating="summary.average"
                    :count="summary.count"
                />
            </div>
        </div>
        <div class="reviews-layout">
            <div>
                <LoadingState v-if="loading" />
                <div
                    v-else-if="listError"
                    class="alert error"
                    role="alert"
                >
                    <p>{{ listError }}</p>
                    <button
                        class="text-link"
                        @click="load()"
                    >
                        Повторить загрузку
                    </button>
                </div>
                <template v-else>
                    <div
                        v-if="!reviews.length"
                        class="review-empty"
                    >
                        <h3>Первый отзыв может быть твоим</h3>
                        <p class="muted">Расскажи, как оборудование показало себя в работе и игре.</p>
                    </div>
                    <article
                        v-for="review in reviews"
                        :key="review.id"
                        class="review-card"
                    >
                        <div class="review-card-heading">
                            <strong>{{ review.author }}</strong>
                            <time
                                :datetime="review.created_at"
                                class="muted"
                            >
                                {{ date(review.created_at) }}
                            </time>
                        </div>
                        <RatingStars :rating="review.rating" />
                        <p class="review-body">{{ review.body }}</p>
                        <span class="verified-review">
                            <BadgeCheck :size="16" />
                            Покупка подтверждена
                        </span>
                    </article>
                    <Pagination
                        :page="meta.current_page"
                        :last="meta.last_page"
                        label="Страницы отзывов"
                        @change="load"
                    />
                </template>
            </div>
            <aside class="review-form-panel">
                <h3>{{ own ? 'Твой отзыв' : 'Как тебе товар?' }}</h3>
                <LoadingState v-if="!auth.ready || contextLoading" />
                <template v-else-if="!auth.user">
                    <p class="muted">Отзывы доступны покупателям после получения заказа.</p>
                    <RouterLink
                        class="button secondary"
                        :to="{name: 'login', query: {redirect: route.path + '#reviews'}}"
                    >
                        Войти и оставить отзыв
                    </RouterLink>
                </template>
                <div
                    v-else-if="contextError"
                    class="alert error"
                    role="alert"
                >
                    <p>{{ contextError }}</p>
                    <button
                        class="text-link"
                        @click="loadOwn"
                    >
                        Повторить проверку покупки
                    </button>
                </div>
                <template v-else-if="eligible">
                    <template v-if="own">
                        <span
                            class="review-status"
                            :class="own.status"
                        >
                            {{ labels[own.status] }}
                        </span>
                        <p
                            v-if="own.status === 'pending'"
                            class="muted"
                        >
                            Отзыв сохранён. Сейчас его видишь только ты и администратор. Рейтинг обновится
                            после публикации.
                        </p>
                        <p
                            v-if="own.moderation_note"
                            class="alert info"
                        >
                            {{ own.moderation_note }}
                        </p>
                    </template>
                    <template v-if="own && !editing">
                        <RatingStars :rating="own.rating" />
                        <p class="review-body">{{ own.body }}</p>
                        <button
                            class="button secondary compact"
                            @click="edit"
                        >
                            Изменить отзыв
                        </button>
                    </template>
                    <form
                        v-else
                        class="form-stack"
                        @submit.prevent="save"
                    >
                        <fieldset
                            class="rating-picker"
                            :disabled="busy"
                        >
                            <legend>Твоя оценка</legend>
                            <div class="rating-choices">
                                <label
                                    v-for="star in 5"
                                    :key="star"
                                    class="rating-choice"
                                    :class="{chosen: form.rating >= star}"
                                >
                                    <input
                                        v-model.number="form.rating"
                                        type="radio"
                                        name="review-rating"
                                        :value="star"
                                        :aria-label="'Оценка ' + star + ' из 5'"
                                        required
                                    />
                                    <Star
                                        :size="29"
                                        aria-hidden="true"
                                    />
                                </label>
                            </div>
                        </fieldset>
                        <label class="field-label">
                            Впечатления от товара
                            <textarea
                                v-model="form.body"
                                rows="5"
                                minlength="10"
                                maxlength="2000"
                                required
                                :disabled="busy"
                                placeholder="Что понравилось, что можно улучшить?"
                            ></textarea>
                        </label>
                        <span class="fine-print">{{ form.body.length }} / 2000</span>
                        <p class="fine-print">
                            Опубликуем имя из профиля и текст после проверки. Не указывай личные контакты.
                            Изменённый отзыв проходит проверку заново.
                        </p>
                        <p
                            v-if="formError"
                            class="alert error"
                            role="alert"
                        >
                            {{ formError }}
                        </p>
                        <button
                            v-if="conflict"
                            type="button"
                            class="button secondary"
                            @click="loadOwn"
                        >
                            Загрузить актуальный отзыв
                        </button>
                        <div class="review-actions">
                            <button
                                class="button primary"
                                :disabled="busy || !form.rating || conflict"
                            >
                                {{ busy ? 'Сохраняем…' : 'Отправить на проверку' }}
                            </button>
                            <button
                                v-if="own"
                                type="button"
                                class="text-link"
                                :disabled="busy"
                                @click="editing = false"
                            >
                                Отмена
                            </button>
                        </div>
                    </form>
                </template>
                <template v-else>
                    <p class="muted">
                        Отзыв можно оставить, когда заказ с этим товаром получен. Так оценки основаны на опыте
                        покупателей.
                    </p>
                    <RouterLink
                        to="/account"
                        class="text-link accent"
                    >
                        Мои заказы
                    </RouterLink>
                </template>
            </aside>
        </div>
    </section>
</template>
