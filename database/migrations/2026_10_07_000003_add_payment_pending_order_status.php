<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires rebuilding the enum; SQLite treats this as a no-op.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('payment_pending','confirmed','packing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'payment_pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('confirmed','packing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'confirmed'");
        }
    }
};
