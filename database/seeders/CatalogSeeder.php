<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Демонстрационные товары. Все цены указаны в копейках.
        $catalog = [
            [
                'name' => 'Клавиатуры',
                'slug' => 'keyboards',
                'products' => [
                    [
                        'name' => 'Механическая клавиатура RESPAWN Pulse 75',
                        'slug' => 'respawn-pulse-75',
                        'description' => 'Механическая клавиатура формата 75% с RGB-подсветкой, сменными переключателями и подключением по USB-C.',
                        'price' => 799_000,
                        'stock' => 14,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Беспроводная клавиатура RESPAWN Air TKL',
                        'slug' => 'respawn-air-tkl',
                        'description' => 'Клавиатура без цифрового блока с подключением по Bluetooth, радиоканалу или кабелю. Подходит для работы и игр.',
                        'price' => 1_099_000,
                        'stock' => 8,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Компактная клавиатура RESPAWN Mini 60',
                        'slug' => 'respawn-mini-60',
                        'description' => 'Компактная механическая клавиатура формата 60% с подсветкой и съёмным кабелем. Демонстрационный товар, ожидающий публикации.',
                        'price' => 549_000,
                        'stock' => 10,
                        'is_active' => false,
                    ],
                ],
            ],
            [
                'name' => 'Мыши',
                'slug' => 'mice',
                'products' => [
                    [
                        'name' => 'Игровая мышь RESPAWN Aim',
                        'slug' => 'respawn-aim',
                        'description' => 'Проводная игровая мышь с оптическим сенсором, шестью кнопками и настройкой чувствительности.',
                        'price' => 249_000,
                        'stock' => 24,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Беспроводная мышь RESPAWN Aim Pro',
                        'slug' => 'respawn-aim-pro',
                        'description' => 'Лёгкая беспроводная мышь с подключением по радиоканалу и зарядкой по USB-C. Подходит для динамичных игр.',
                        'price' => 599_000,
                        'stock' => 0,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Мышь RESPAWN Silent',
                        'slug' => 'respawn-silent',
                        'description' => 'Универсальная мышь с тихими кнопками, симметричным корпусом и подключением по Bluetooth.',
                        'price' => 199_000,
                        'stock' => 18,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'Кресла',
                'slug' => 'chairs',
                'products' => [
                    [
                        'name' => 'Игровое кресло RESPAWN Core',
                        'slug' => 'respawn-core-chair',
                        'description' => 'Кресло с тканевой обивкой, регулировкой высоты, наклона спинки и поясничной подушкой.',
                        'price' => 1_899_000,
                        'stock' => 7,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Игровое кресло RESPAWN Pro',
                        'slug' => 'respawn-pro-chair',
                        'description' => 'Кресло для длительной работы и игр с регулируемыми подлокотниками, подголовником и механизмом качания.',
                        'price' => 2_799_000,
                        'stock' => 4,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Игровое кресло RESPAWN XL',
                        'slug' => 'respawn-xl-chair',
                        'description' => 'Кресло с широким сиденьем, усиленным основанием, высокой спинкой и регулируемой поясничной поддержкой.',
                        'price' => 3_499_000,
                        'stock' => 3,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'Компьютеры',
                'slug' => 'computers',
                'products' => [
                    [
                        'name' => 'Игровой компьютер RESPAWN Start',
                        'slug' => 'respawn-start-pc',
                        'description' => 'Начальная игровая сборка с шестиядерным процессором, 16 ГБ оперативной памяти, SSD на 1 ТБ и дискретной видеокартой.',
                        'price' => 7_999_000,
                        'stock' => 5,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Игровой компьютер RESPAWN Boost',
                        'slug' => 'respawn-boost-pc',
                        'description' => 'Игровая сборка с восьмиядерным процессором, 32 ГБ оперативной памяти, SSD на 2 ТБ и корпусом с RGB-подсветкой.',
                        'price' => 12_999_000,
                        'stock' => 3,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Игровой компьютер RESPAWN Ultra',
                        'slug' => 'respawn-ultra-pc',
                        'description' => 'Сборка для требовательных игр и творческих задач с 64 ГБ оперативной памяти, SSD на 2 ТБ и жидкостным охлаждением процессора.',
                        'price' => 21_999_000,
                        'stock' => 2,
                        'is_active' => true,
                    ],
                ],
            ],
        ];

        $extra = [
            'keyboards' => [
                [
                    'RESPAWN Phantom 98',
                    'respawn-phantom-98',
                    1_299_000,
                    'Полноразмерная механика, тихие переключатели и двойная шумоизоляция.',
                ],
                [
                    'RESPAWN Neon Split',
                    'respawn-neon-split',
                    1_599_000,
                    'Эргономичная раздельная клавиатура с программируемыми слоями.',
                ],
                [
                    'RESPAWN Edge Low',
                    'respawn-edge-low',
                    899_000,
                    'Низкопрофильная механическая клавиатура для компактного рабочего места.',
                ],
            ],
            'mice' => [
                [
                    'RESPAWN Vector',
                    'respawn-vector',
                    399_000,
                    'Точный сенсор, лёгкий корпус и гибкий кабель для соревновательных игр.',
                ],
                [
                    'RESPAWN Orbit',
                    'respawn-orbit',
                    749_000,
                    'Беспроводная мышь с магнитной зарядной станцией и боковыми кнопками.',
                ],
                [
                    'RESPAWN Drift',
                    'respawn-drift',
                    459_000,
                    'Удобная форма для правой руки и настройка чувствительности на корпусе.',
                ],
            ],
            'chairs' => [
                [
                    'RESPAWN Mesh',
                    'respawn-mesh-chair',
                    2_299_000,
                    'Дышащая сетчатая спинка, поясничная поддержка и регулируемый подголовник.',
                ],
                [
                    'RESPAWN Studio',
                    'respawn-studio-chair',
                    3_199_000,
                    'Тканевая обивка, широкое сиденье и плавная настройка положения спинки.',
                ],
                [
                    'RESPAWN Throne',
                    'respawn-throne-chair',
                    4_299_000,
                    'Флагманское кресло с синхромеханизмом и регулируемыми подлокотниками.',
                ],
            ],
            'computers' => [
                [
                    'RESPAWN Mini',
                    'respawn-mini-pc',
                    9_999_000,
                    'Компактный игровой компьютер с 32 ГБ памяти и SSD на 1 ТБ.',
                ],
                [
                    'RESPAWN Creator',
                    'respawn-creator-pc',
                    17_999_000,
                    'Рабочая станция для монтажа и игр, 64 ГБ памяти и SSD на 2 ТБ.',
                ],
                [
                    'RESPAWN Apex',
                    'respawn-apex-pc',
                    24_999_000,
                    'Игровая система с жидкостным охлаждением, 64 ГБ памяти и SSD на 4 ТБ.',
                ],
            ],
        ];

        foreach ($catalog as &$categoryData) {
            foreach ($extra[$categoryData['slug']] as [$name, $slug, $price, $description]) {
                $categoryData['products'][] = compact('name', 'slug', 'price', 'description') + [
                    'stock' => 8,
                    'is_active' => true,
                ];
            }
        }

        unset($categoryData);

        DB::transaction(function () use ($catalog): void {
            foreach ($catalog as $categoryData) {
                $category = Category::updateOrCreate(
                    [
                        'slug' => $categoryData['slug'],
                    ],
                    [
                        'name' => $categoryData['name'],
                    ],
                );

                foreach ($categoryData['products'] as $productData) {

                    $kind = match ($categoryData['slug']) {
                        'mice' => 'mouse',
                        'chairs' => 'chair',
                        'computers' => 'pc',
                        default => 'keyboard',
                    };
                    $productData += [
                        'sku' => 'RSP-' . strtoupper(substr(hash('sha256', $productData['slug']), 0, 8)),
                        'brand' => 'RESPAWN',
                        'kind' => $kind,
                        'badge' => str_contains($productData['slug'], 'pro') ? 'PRO SERIES' : null,
                        'is_featured' => in_array(
                            $productData['slug'],
                            [
                                'respawn-pulse-75',
                                'respawn-aim',
                                'respawn-core-chair',
                                'respawn-boost-pc',
                            ],
                            true
                        ),
                        'specifications' => match ($kind) {
                            'keyboard' => [
                                'Подключение' => 'USB-C',
                                'Подсветка' => 'RGB',
                                'Раскладка' => 'RU / EN',
                                'Гарантия' => '12 месяцев',
                            ],
                            'mouse' => [
                                'Сенсор' => 'Оптический',
                                'Настройка DPI' => 'Да',
                                'Кнопки' => '6',
                                'Гарантия' => '12 месяцев',
                            ],
                            'chair' => [
                                'Высота' => 'Регулируемая',
                                'Подлокотники' => 'Регулируемые',
                                'Сборка' => 'Самостоятельная',
                                'Гарантия' => '24 месяца',
                            ],
                            default => [
                                'Накопитель' => 'SSD NVMe',
                                'Корпус' => 'С RGB-подсветкой',
                                'Система' => 'Без ОС',
                                'Гарантия' => '24 месяца',
                            ],
                        },
                    ];

                    $category->products()->updateOrCreate(
                        [
                            'slug' => $productData['slug'],
                        ],
                        $productData,
                    );
                }
            }
        });
    }
}
