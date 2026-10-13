<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_registrations', function (Blueprint $table) {
            $table->string('owner_name')->nullable()->after('vendor_name');
            $table->string('phone')->nullable()->after('owner_name');
            $table->string('email')->nullable()->after('phone');
            $table->string('shop_number')->nullable()->after('market');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_registrations', function (Blueprint $table) {
            $table->dropColumn(['owner_name', 'phone', 'email', 'shop_number']);
        });
    }
};
