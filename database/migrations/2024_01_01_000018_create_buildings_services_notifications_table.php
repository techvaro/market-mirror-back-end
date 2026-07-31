<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('location');
            $table->unsignedInteger('shop_count')->default(0);
            $table->string('category');
            $table->string('banner_gradient')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('category');
            $table->unsignedBigInteger('price');
            $table->decimal('rating', 3, 1)->default(0);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->string('link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('services');
        Schema::dropIfExists('buildings');
    }
};
