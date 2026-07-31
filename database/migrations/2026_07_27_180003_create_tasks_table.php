<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapper_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['vendor_registration', 'shop_mapping', 're_mapping', 'verification_visit'])->nullable();
            $table->string('market');
            $table->string('street')->nullable();
            $table->string('shop_block')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->integer('daily_target')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('corrections')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
