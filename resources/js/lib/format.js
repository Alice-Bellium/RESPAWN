export const money = (value) =>
    new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        maximumFractionDigits: 0,
    }).format(Number(value || 0) / 100);

export const date = (value) =>
    new Intl.DateTimeFormat('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));

export const statusLabels = {
    processing: 'Собираем заказ',
    shipped: 'Передан в доставку',
    completed: 'Получен',
    cancelled: 'Отменён',
};

export function readStorage(key, fallback) {
    try {
        return JSON.parse(localStorage.getItem(key)) ?? fallback;
    } catch {
        return fallback;
    }
}

export function writeStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch {
        /* Private browsing or full storage must not break shopping. */
    }
}

export function safeRedirect(path) {
    return typeof path === 'string' && path.startsWith('/') && !path.startsWith('//') && !path.includes('\\')
        ? path
        : '/account';
}
