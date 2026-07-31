<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_queue', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('market');
            $table->foreignId('mapper_id')->constrained()->onDelete('cascade');
            $table->string('mapper_name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_requested'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_queue');
    }
};
