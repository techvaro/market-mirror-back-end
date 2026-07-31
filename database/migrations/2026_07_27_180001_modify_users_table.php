<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['buyer', 'vendor', 'admin', 'mapper'])->default('buyer')->change();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active')->after('role');
            $table->string('avatar_url')->nullable()->after('status');
            $table->string('employee_id')->nullable()->after('avatar_url');
            $table->string('region')->nullable()->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'avatar_url', 'employee_id', 'region']);
            $table->enum('role', ['buyer', 'vendor'])->default('buyer')->change();
        });
    }
};
