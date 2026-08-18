<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->foreignId('resolved_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('resolution_note')->nullable()->after('resolved_by');
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->enum('status', ['Under Review', 'Resolved', 'Closed', 'Refunded', 'Released', 'Escalated'])
                ->default('Under Review')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['resolved_by', 'resolution_note']);
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->enum('status', ['Under Review', 'Resolved', 'Closed'])->default('Under Review')->change();
        });
    }
};
