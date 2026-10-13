<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_applications', function (Blueprint $table) {
            $table->string('building_name')->nullable()->after('shop_name');
            $table->string('shop_number')->nullable()->after('building_name');
            $table->string('shop_address')->nullable()->after('shop_number');
            $table->string('id_document_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_applications', function (Blueprint $table) {
            $table->dropColumn(['building_name', 'shop_number', 'shop_address', 'id_document_path']);
        });
    }
};
