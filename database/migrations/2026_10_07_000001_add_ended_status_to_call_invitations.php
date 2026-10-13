<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_invitations', function (Blueprint $table) {
            $table->enum('status', ['ringing', 'accepted', 'declined', 'missed', 'ended'])->default('ringing')->change();
        });
    }

    public function down(): void
    {
        Schema::table('call_invitations', function (Blueprint $table) {
            $table->enum('status', ['ringing', 'accepted', 'declined', 'missed'])->default('ringing')->change();
        });
    }
};
