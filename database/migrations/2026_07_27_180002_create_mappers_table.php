<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mappers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('assigned_market');
            $table->string('assigned_region');
            $table->enum('mapper_status', ['active', 'inactive', 'suspended'])->default('active');
            $table->enum('gps_status', ['online', 'offline', 'idle'])->default('offline');
            $table->integer('tasks_assigned')->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_pending')->default(0);
            $table->integer('tasks_rejected')->default(0);
            $table->decimal('approval_rate', 5, 1)->default(0);
            $table->decimal('avg_completion_time_hrs', 4, 1)->default(0);
            $table->decimal('distance_covered_km', 8, 1)->default(0);
            $table->string('supervisor')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mappers');
    }
};
