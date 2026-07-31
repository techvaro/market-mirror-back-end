<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category');
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('product_count')->default(0);
            $table->text('description');
            $table->string('location');
            $table->string('hours')->nullable();
            $table->string('banner_gradient')->nullable();
            $table->string('market');
            $table->string('city');
            $table->string('phone');
            $table->string('shop_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
