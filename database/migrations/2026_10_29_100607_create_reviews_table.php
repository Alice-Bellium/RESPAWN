<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('revision')->default(1);
            $table->string('moderation_note', 500)->nullable();
            $table
                ->foreignId('moderated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
            $table->index(['product_id', 'status', 'id']);
            $table->index(['status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
