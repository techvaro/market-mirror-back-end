<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shop_name');
            $table->string('owner_name');
            $table->string('phone');
            $table->string('category');
            $table->string('market');
            $table->text('description');
            $table->enum('status', ['Pending Review', 'Approved', 'Rejected'])->default('Pending Review');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_applications');
    }
};
