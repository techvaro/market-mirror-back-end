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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->enum('status', ['confirmed', 'packing', 'out_for_delivery', 'delivered', 'cancelled'])->default('confirmed');
            $table->enum('delivery_method', ['delivery', 'pickup'])->default('delivery');
            $table->string('payment_method')->default('paystack');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('estimated_delivery')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->string('address_first_name')->nullable();
            $table->string('address_last_name')->nullable();
            $table->string('address_phone')->nullable();
            $table->text('address_street')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_city')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('variant');
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('quantity');
            $table->string('color')->nullable();
            $table->string('shop_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
