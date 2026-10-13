<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('banner_gradient');
            $table->foreignId('state_id')->nullable()->after('id')
                ->constrained('states')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('state_id');
            $table->dropColumn('is_active');
        });
    }
};
