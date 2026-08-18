<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        DB::table('markets')->where('name', 'Alaba International Market')->update([
            'latitude' => 6.4478,
            'longitude' => 3.1884,
        ]);

        DB::table('markets')->where('name', 'Computer Village')->update([
            'latitude' => 6.5961,
            'longitude' => 3.3417,
        ]);

        DB::table('markets')->where('name', 'Trade Fair Complex')->update([
            'latitude' => 6.4761,
            'longitude' => 3.2777,
        ]);
    }

    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
