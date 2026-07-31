<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('original_price');
            $table->unsignedBigInteger('sale_price');
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('sold_count')->default(0);
            $table->integer('stock_count')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sales');
    }
};
