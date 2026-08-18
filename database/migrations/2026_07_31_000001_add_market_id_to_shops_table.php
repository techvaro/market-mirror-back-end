<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->foreignId('market_id')
                ->nullable()
                ->after('user_id')
                ->constrained('markets')
                ->nullOnDelete();
        });

        DB::statement('
            UPDATE shops
            SET market_id = (
                SELECT id FROM markets WHERE markets.name = shops.market
            )
            WHERE market_id IS NULL
        ');

        DB::statement('
            UPDATE markets
            SET shop_count = (
                SELECT COUNT(*) FROM shops WHERE shops.market_id = markets.id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropColumn('market_id');
        });
    }
};
