<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreignId('mapper_id')->nullable()->constrained()->onDelete('set null');
            $table->string('vendor_name');
            $table->string('market');
            $table->string('street')->nullable();
            $table->string('shop_block')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->enum('accuracy', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected', 're_mapping_requested'])->default('pending');
            $table->string('mapper_name')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_locations');
    }
};
