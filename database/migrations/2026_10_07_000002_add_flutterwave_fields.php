<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider_transaction_id')->nullable()->unique()->after('reference');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->string('bank_code')->nullable()->after('bank_name');
            $table->string('transfer_reference')->nullable()->unique()->after('status');
            $table->json('provider_response')->nullable()->after('transfer_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->dropUnique(['provider_transaction_id']));
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('provider_transaction_id'));
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropUnique(['transfer_reference']);
            $table->dropColumn(['bank_code', 'transfer_reference', 'provider_response']);
        });
    }
};
