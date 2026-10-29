<script setup>
import {onMounted, ref} from 'vue';
import {Plus, Pencil, EyeOff, ArrowUpRight} from '@lucide/vue';
import {api, errorMessage} from '../lib/api';
import {money, date, statusLabels} from '../lib/format';
import {useUiStore} from '../stores/ui';
import Modal from '../components/Modal.vue';
import Pagination from '../components/Pagination.vue';
import LoadingState from '../components/LoadingState.vue';
import EmptyState from '../components/EmptyState.vue';

const ui = useUiStore();
const tab = ref('products');
const stats = ref({});
const products = ref([]);
const orders = ref([]);
const categories = ref([]);
const meta = ref({});
const search = ref('');
const status = ref('');
const loading = ref(false);
const busy = ref(false);
const error = ref('');
const modalError = ref('');
const editing = ref(null);
const specs = ref('');
const selectedOrder = ref(null);
const archive = ref(null);
let version = 0;

async function load(page = 1) {
    const current = ++version;
    loading.value = true;
    error.value = '';
    const currentTab = tab.value;
    try {
        const {data} = await api.get('/admin/' + currentTab, {
            params: {page, ...(currentTab === 'products' ? {q: search.value} : {status: status.value})},
        });
        if (current !== version) return;
        if (currentTab === 'products') {
            products.value = data.data;
            meta.value = data;
        } else {
            orders.value = data.data;
            meta.value = data.meta;
        }
    } catch (e) {
        if (current === version) error.value = errorMessage(e);
    } finally {
        if (current === version) loading.value = false;
    }
}
async function summary() {
    stats.value = (await api.get('/admin/summary')).data;
}
onMounted(async () => {
    load();
    try {
        await summary();
        categories.value = (await api.get('/categories')).data.data;
    } catch (e) {
        error.value = errorMessage(e);
    }
});
function changeTab(value) {
    tab.value = value;
    load();
}
function edit(product = null) {
    modalError.value = '';
    editing.value = product
        ? {...product, price: product.price / 100}
        : {
              name: '',
              slug: '',
              sku: '',
              brand: 'RESPAWN',
              category_id: categories.value[0]?.id,
              kind: 'keyboard',
              price: '',
              stock: 0,
              description: '',
              is_active: false,
              is_featured: false,
              badge: '',
          };
    specs.value = JSON.stringify(product?.specifications || {}, null, 2);
}
async function save() {
    if (busy.value) return;
    modalError.value = '';
    let specifications;
    try {
        specifications = JSON.parse(specs.value || '{}');
        if (!specifications || Array.isArray(specifications) || typeof specifications !== 'object')
            throw new Error();
        if (Object.keys(specifications).some((key) => !key.trim() || key.length > 100)) throw new Error();
    } catch {
        modalError.value = 'Характеристики: укажите JSON-объект с названиями и строковыми значениями.';
        return;
    }
    busy.value = true;
    const data = {...editing.value, price: Math.round(Number(editing.value.price) * 100), specifications};
    try {
        if (data.id) await api.put('/admin/products/' + data.id, data);
        else await api.post('/admin/products', data);
        editing.value = null;
        ui.notify('Товар сохранён');
        await Promise.all([load(), summary()]);
    } catch (e) {
        modalError.value = errorMessage(e);
    } finally {
        busy.value = false;
    }
}
async function hide() {
    busy.value = true;
    modalError.value = '';
    try {
        await api.delete('/admin/products/' + archive.value.id);
        archive.value = null;
        ui.notify('Товар скрыт');
        await load();
    } catch (e) {
        modalError.value = errorMessage(e);
    } finally {
        busy.value = false;
    }
}
async function transition(order, value) {
    busy.value = true;
    modalError.value = '';
    try {
        const {data} = await api.patch('/admin/orders/' + order.id, {status: value});
        selectedOrder.value = data.data;
        ui.notify('Статус обновлён');
        await Promise.all([load(meta.value.current_page), summary()]);
    } catch (e) {
        modalError.value = errorMessage(e);
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <main class="container page">
        <div class="page-heading">
            <div>
                <span class="eyebrow accent">RESPAWN CONTROL ROOM</span>
                <h1>УПРАВЛЕНИЕ МАГАЗИНОМ</h1>
            </div>
            <RouterLink
                to="/account"
                class="text-link"
            >
                В кабинет
                <ArrowUpRight :size="17" />
            </RouterLink>
        </div>
        <div class="admin-stats">
            <div class="stat-card">
                <span>Товаров</span>
                <strong>{{ stats.products || 0 }}</strong>
            </div>
            <div class="stat-card">
                <span>Заказов</span>
                <strong>{{ stats.orders || 0 }}</strong>
            </div>
            <div class="stat-card">
                <span>Ждут обработки</span>
                <strong>{{ stats.processing || 0 }}</strong>
            </div>
            <div class="stat-card">
                <span>Сумма демо-заказов</span>
                <strong>{{ money(stats.revenue) }}</strong>
            </div>
        </div>
        <nav
            class="tabs"
            aria-label="Управление магазином"
        >
            <button
                :class="{active: tab === 'products'}"
                @click="changeTab('products')"
            >
                Товары
            </button>
            <button
                :class="{active: tab === 'orders'}"
                @click="changeTab('orders')"
            >
                Заказы
            </button>
        </nav>
        <div class="catalog-toolbar">
            <form
                v-if="tab === 'products'"
                class="search-field"
                @submit.prevent="load()"
            >
                <input
                    v-model="search"
                    type="search"
                    placeholder="Название товара"
                    aria-label="Поиск в админке"
                />
                <button
                    class="text-link"
                    type="submit"
                >
                    Найти
                </button>
            </form>
            <select
                v-else
                v-model="status"
                aria-label="Статус заказа"
                @change="load()"
            >
                <option value="">Все статусы</option>
                <option
                    v-for="(label, value) in statusLabels"
                    :key="value"
                    :value="value"
                >
                    {{ label }}
                </option>
            </select>
            <button
                v-if="tab === 'products'"
                class="button primary"
                @click="edit()"
            >
                <Plus :size="18" />
                Добавить товар
            </button>
        </div>
        <p
            v-if="error"
            class="alert error"
        >
            {{ error }}
        </p>
        <LoadingState v-if="loading" />
        <template v-else-if="tab === 'products'">
            <EmptyState
                v-if="!products.length"
                title="Товары не найдены"
                text="Измени поиск или добавь новый товар."
            />
            <div
                v-else
                class="table-scroll"
            >
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Цена</th>
                            <th>Остаток</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products"
                            :key="product.id"
                        >
                            <td>
                                <div class="table-product">
                                    <img
                                        :src="'/images/products/' + product.kind + '.svg'"
                                        alt=""
                                    />
                                    <div>
                                        <b>{{ product.name }}</b>
                                        <div class="muted">{{ product.category.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ money(product.price) }}</td>
                            <td>{{ product.stock }} шт.</td>
                            <td>
                                <span :class="product.is_active ? 'stock' : 'muted'">
                                    {{ product.is_active ? 'Опубликован' : 'Скрыт' }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button
                                        class="icon-button"
                                        :aria-label="'Редактировать ' + product.name"
                                        @click="edit(product)"
                                    >
                                        <Pencil :size="18" />
                                    </button>
                                    <button
                                        v-if="product.is_active"
                                        class="icon-button"
                                        :aria-label="'Скрыть ' + product.name"
                                        @click="
                                            archive = product;
                                            modalError = '';
                                        "
                                    >
                                        <EyeOff :size="18" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
        <template v-else>
            <EmptyState
                v-if="!orders.length"
                title="Таких заказов пока нет"
                text="Новые заказы появятся после оформления в магазине."
            />
            <article
                v-for="order in orders"
                :key="order.id"
                class="order-card"
            >
                <div class="order-card-top">
                    <div>
                        <h3>{{ order.customer_name }} · {{ date(order.created_at) }}</h3>
                        <span class="order-number">{{ order.number }}</span>
                    </div>
                    <span
                        class="order-status"
                        :class="order.status"
                    >
                        {{ statusLabels[order.status] }}
                    </span>
                </div>
                <div class="order-card-bottom">
                    <strong>{{ money(order.total) }}</strong>
                    <button
                        class="button secondary compact"
                        @click="
                            selectedOrder = order;
                            modalError = '';
                        "
                    >
                        Открыть заказ
                        <ArrowUpRight :size="16" />
                    </button>
                </div>
            </article>
        </template>
        <Pagination
            v-if="!loading"
            :page="meta.current_page"
            :last="meta.last_page"
            @change="load"
        />

        <Modal
            v-if="editing"
            :title="editing.id ? 'Редактировать товар' : 'Новый товар'"
            @close="!busy && (editing = null)"
        >
            <form
                class="form-stack"
                @submit.prevent="save"
            >
                <label class="field-label">
                    Название
                    <input
                        v-model="editing.name"
                        required
                        maxlength="150"
                    />
                </label>
                <div class="form-grid">
                    <label class="field-label">
                        Slug (латиница и дефисы)
                        <input
                            v-model="editing.slug"
                            required
                            pattern="[a-z0-9]+(-[a-z0-9]+)*"
                            maxlength="150"
                            placeholder="respawn-pulse-75"
                        />
                    </label>
                    <label class="field-label">
                        Артикул
                        <input
                            v-model="editing.sku"
                            maxlength="40"
                        />
                    </label>
                </div>
                <div class="form-grid">
                    <label class="field-label">
                        Категория
                        <select
                            v-model="editing.category_id"
                            required
                        >
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </label>
                    <label class="field-label">
                        Иллюстрация
                        <select v-model="editing.kind">
                            <option value="keyboard">Клавиатура</option>
                            <option value="mouse">Мышь</option>
                            <option value="chair">Кресло</option>
                            <option value="pc">Компьютер</option>
                        </select>
                    </label>
                </div>
                <div class="form-grid">
                    <label class="field-label">
                        Цена, ₽
                        <input
                            v-model="editing.price"
                            type="number"
                            min="1"
                            max="1000000"
                            step="0.01"
                            required
                        />
                    </label>
                    <label class="field-label">
                        Остаток, шт.
                        <input
                            v-model.number="editing.stock"
                            type="number"
                            min="0"
                            max="100000"
                            step="1"
                            required
                        />
                    </label>
                </div>
                <div class="form-grid">
                    <label class="field-label">
                        Бренд
                        <input
                            v-model="editing.brand"
                            required
                            maxlength="80"
                        />
                    </label>
                    <label class="field-label">
                        Метка на карточке
                        <input
                            v-model="editing.badge"
                            maxlength="40"
                            placeholder="PRO SERIES"
                        />
                    </label>
                </div>
                <label class="field-label">
                    Описание
                    <textarea
                        v-model="editing.description"
                        rows="3"
                        maxlength="10000"
                    ></textarea>
                </label>
                <label class="field-label">
                    Характеристики, JSON
                    <textarea
                        v-model="specs"
                        rows="5"
                        spellcheck="false"
                    ></textarea>
                </label>
                <p
                    class="muted"
                    style="font-size: 11px; margin: 0"
                >
                    Например: {"Подключение": "USB-C", "Вес": "850 г"}
                </p>
                <div class="form-grid">
                    <label
                        class="check-row"
                        style="margin: 0"
                    >
                        <input
                            v-model="editing.is_active"
                            type="checkbox"
                        />
                        Показывать в каталоге
                    </label>
                    <label
                        class="check-row"
                        style="margin: 0"
                    >
                        <input
                            v-model="editing.is_featured"
                            type="checkbox"
                        />
                        На главной странице
                    </label>
                </div>
                <p
                    v-if="modalError"
                    role="alert"
                    class="alert error"
                >
                    {{ modalError }}
                </p>
                <button
                    class="button primary"
                    :disabled="busy"
                >
                    {{ busy ? 'Сохраняем…' : 'Сохранить товар' }}
                </button>
            </form>
        </Modal>
        <Modal
            v-if="archive"
            title="Скрыть товар?"
            @close="!busy && (archive = null)"
        >
            <p class="muted">
                {{ archive.name }} исчезнет из каталога. В старых заказах информация останется. Вернуть товар
                можно через редактирование.
            </p>
            <p
                v-if="modalError"
                class="alert error"
            >
                {{ modalError }}
            </p>
            <button
                class="button primary"
                :disabled="busy"
                @click="hide"
            >
                Скрыть товар
            </button>
        </Modal>
        <Modal
            v-if="selectedOrder"
            title="Детали заказа"
            @close="!busy && (selectedOrder = null)"
        >
            <p class="order-number">{{ selectedOrder.number }}</p>
            <span
                class="order-status"
                :class="selectedOrder.status"
            >
                {{ statusLabels[selectedOrder.status] }}
            </span>
            <div class="info-grid">
                <div>
                    <h3>Получатель</h3>
                    <p>
                        {{ selectedOrder.customer_name }}
                        <br />
                        {{ selectedOrder.email }}
                        <br />
                        {{ selectedOrder.phone }}
                    </p>
                </div>
                <div>
                    <h3>{{ selectedOrder.delivery_method === 'delivery' ? 'Доставка' : 'Самовывоз' }}</h3>
                    <p>{{ selectedOrder.address || 'Демо-пункт выдачи' }}</p>
                    <p>{{ selectedOrder.note }}</p>
                </div>
            </div>
            <div
                v-for="item in selectedOrder.items"
                :key="item.id"
                class="summary-row"
            >
                <span>{{ item.name }} × {{ item.quantity }}</span>
                <b>{{ money(item.total) }}</b>
            </div>
            <div class="summary-row">
                <span>Доставка</span>
                <b>{{ money(selectedOrder.delivery_total) }}</b>
            </div>
            <div class="summary-row total">
                <span>Итого</span>
                <b>{{ money(selectedOrder.total) }}</b>
            </div>
            <p class="alert info">Учебная оплата. Реальной финансовой операции нет.</p>
            <p
                v-if="modalError"
                class="alert error"
            >
                {{ modalError }}
            </p>
            <div
                class="hero-actions"
                style="margin-top: 15px; flex-wrap: wrap"
            >
                <button
                    v-if="selectedOrder.status === 'processing'"
                    class="button primary"
                    :disabled="busy"
                    @click="transition(selectedOrder, 'shipped')"
                >
                    Передать в доставку
                </button>
                <button
                    v-if="selectedOrder.status === 'shipped'"
                    class="button primary"
                    :disabled="busy"
                    @click="transition(selectedOrder, 'completed')"
                >
                    Подтвердить получение
                </button>
                <button
                    v-if="selectedOrder.status === 'processing'"
                    class="button secondary"
                    :disabled="busy"
                    @click="transition(selectedOrder, 'cancelled')"
                >
                    Отменить заказ
                </button>
            </div>
        </Modal>
    </main>
</template>
