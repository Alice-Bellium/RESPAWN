<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('number', 40)->unique();
            $table->uuid('idempotency_key');
            $table->string('request_hash', 64);
            $table
                ->string('status', 20)
                ->default('processing')
                ->index();
            $table->string('customer_name', 100);
            $table->string('email', 190);
            $table->string('phone', 30);
            $table->string('delivery_method', 20);
            $table->string('address', 500)->nullable();
            $table->string('note', 1000)->nullable();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('delivery_total');
            $table->unsignedBigInteger('total');
            $table->string('payment_method', 20)->default('demo');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
