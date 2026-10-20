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
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 30)->unique();
            $table->decimal('subtotal', 20, 2);
            $table->decimal('delivery_fee', 20, 2)->default(0);
            $table->decimal('total', 20, 2);
            $table->string('fulfillment_method', 20);
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 40)->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('status', 40)->default('Menunggu Konfirmasi');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 20, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};