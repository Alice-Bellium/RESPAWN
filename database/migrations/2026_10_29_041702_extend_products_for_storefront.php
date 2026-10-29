<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Добавляет характеристики витрины, сохраняя существующие товары. */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table
                ->string('sku', 40)
                ->nullable()
                ->unique();
            $table->string('brand', 80)->default('RESPAWN');
            $table->string('kind', 20)->default('keyboard');
            $table->string('badge', 40)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('specifications')->nullable();
        });
    }

    /** Удаляет только дополнительные поля витрины. */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn(['sku', 'brand', 'kind', 'badge', 'is_featured', 'specifications']);
        });
    }
};
