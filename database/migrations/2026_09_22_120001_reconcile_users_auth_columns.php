<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'suspended', 'pending'])->default('active');
            }

            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable();
            }

            if (! Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable();
            }

            if (! Schema::hasColumn('users', 'region')) {
                $table->string('region')->nullable();
            }

            if (! Schema::hasColumn('users', 'active_market_id')) {
                $table->unsignedBigInteger('active_market_id')->nullable();
            }

            if (! Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }

            if (! Schema::hasColumn('users', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable();
            }

            if (! Schema::hasColumn('users', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable();
            }
        });

        if (Schema::hasTable('markets') && Schema::hasColumn('users', 'active_market_id')) {
            $hasActiveMarketForeignKey = collect(Schema::getForeignKeys('users'))
                ->contains(fn (array $foreign) => in_array('active_market_id', $foreign['columns'] ?? [], true));

            if (! $hasActiveMarketForeignKey) {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('active_market_id')
                        ->references('id')
                        ->on('markets')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'active_market_id')) {
                $table->dropForeign(['active_market_id']);
            }

            $columns = collect([
                'status',
                'avatar_url',
                'employee_id',
                'region',
                'active_market_id',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
            ])->filter(fn (string $column) => Schema::hasColumn('users', $column))->all();

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
