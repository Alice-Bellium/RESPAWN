<script setup>
import {computed, ref, watch} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import {ArrowRight} from '@lucide/vue';
import {useAuthStore} from '../stores/auth';
import {errorMessage} from '../lib/api';
import {safeRedirect} from '../lib/format';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const register = computed(() => route.name === 'register');
const form = ref({name: '', email: '', password: '', password_confirmation: ''});
const error = ref('');
const busy = ref(false);

watch(register, () => {
    error.value = '';
    form.value.password = '';
    form.value.password_confirmation = '';
});

async function submit() {
    busy.value = true;
    error.value = '';
    try {
        await auth.submit(register.value ? 'register' : 'login', form.value);
        await router.replace(safeRedirect(route.query.redirect));
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <main class="container page">
        <div class="auth-layout">
            <div class="auth-art">
                <span class="eyebrow accent">WELCOME, PLAYER ONE</span>
                <h2>
                    ТВОЯ ИГРА.
                    <br />
                    ТВОЙ АККАУНТ.
                </h2>
                <p class="muted">
                    Сохраняй заказы.
                    <br />
                    Собирай свой следующий сетап.
                </p>
                <img
                    src="/images/products/pc.svg"
                    alt=""
                />
            </div>
            <div class="auth-form">
                <span class="eyebrow accent">RESPAWN ACCOUNT</span>
                <h1 style="margin-top: 18px">{{ register ? 'НОВЫЙ ИГРОК' : 'С ВОЗВРАЩЕНИЕМ' }}</h1>
                <p>
                    {{
                        register
                            ? 'Создай аккаунт, чтобы оформить первый заказ.'
                            : 'Войди, чтобы продолжить с того же места.'
                    }}
                </p>
                <form
                    class="form-stack"
                    @submit.prevent="submit"
                >
                    <label
                        v-if="register"
                        class="field-label"
                    >
                        Твоё имя
                        <input
                            v-model="form.name"
                            required
                            autocomplete="name"
                            maxlength="100"
                        />
                    </label>
                    <label class="field-label">
                        Электронная почта
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            maxlength="190"
                        />
                    </label>
                    <label class="field-label">
                        Пароль
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            :autocomplete="register ? 'new-password' : 'current-password'"
                            :minlength="register ? 10 : 1"
                            maxlength="72"
                        />
                    </label>
                    <template v-if="register">
                        <p
                            class="fine-print"
                            style="margin: -10px 0 0"
                        >
                            Не менее 10 символов, включая буквы и цифры.
                        </p>
                        <label class="field-label">
                            Повтори пароль
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                minlength="10"
                                maxlength="72"
                            />
                        </label>
                    </template>
                    <p
                        v-if="error"
                        role="alert"
                        class="alert error"
                        style="margin: 0"
                    >
                        {{ error }}
                    </p>
                    <button
                        class="button primary full"
                        :disabled="busy"
                    >
                        {{ busy ? 'Подождите…' : register ? 'Создать аккаунт' : 'Войти' }}
                        <ArrowRight :size="18" />
                    </button>
                </form>
                <p class="auth-switch">
                    {{ register ? 'Уже есть аккаунт?' : 'Первый раз здесь?' }}
                    <RouterLink
                        :to="{name: register ? 'login' : 'register', query: route.query}"
                        class="text-link accent"
                    >
                        {{ register ? 'Войти' : 'Зарегистрироваться' }}
                    </RouterLink>
                </p>
                <p class="fine-print">
                    Учебный магазин. Для тестирования можно использовать вымышленные данные.
                </p>
            </div>
        </div>
    </main>
</template>
