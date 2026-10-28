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
