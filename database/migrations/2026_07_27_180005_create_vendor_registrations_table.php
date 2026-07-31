<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('business_category');
            $table->string('market');
            $table->foreignId('mapper_id')->constrained()->onDelete('cascade');
            $table->string('mapper_name');
            $table->enum('current_status', ['draft', 'submitted', 'under_review', 'finalized'])->default('draft');
            $table->enum('approval_status', ['pending_review', 'approved', 'rejected', 'changes_requested'])->default('pending_review');
            $table->json('photos')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_registrations');
    }
};
