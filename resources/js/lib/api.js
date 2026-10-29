import axios from 'axios';

export const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
});

export function setCsrf(token) {
    api.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const request = error.config;
        if (error.response?.status === 419 && request && !request._csrfRetried) {
            request._csrfRetried = true;
            const {data} = await api.get('/session');
            setCsrf(data.csrf_token);
            request.headers['X-CSRF-TOKEN'] = data.csrf_token;
            return api(request);
        }
        return Promise.reject(error);
    },
);

export function errorMessage(error) {
    const errors = error.response?.data?.errors;
    if (errors) return Object.values(errors).flat()[0];
    if (error.response?.status === 429) return 'Слишком много попыток. Подождите минуту.';
    if (error.response?.status >= 500) return 'Сервис временно недоступен. Попробуйте ещё раз.';
    return error.response?.data?.message || 'Не удалось связаться с сервером. Проверьте подключение.';
}
