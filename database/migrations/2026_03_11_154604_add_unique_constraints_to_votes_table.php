<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->unique(['poll_id', 'ip_address'], 'votes_poll_ip_unique');
            $table->unique(['poll_id', 'session_token'], 'votes_poll_session_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique('votes_poll_ip_unique');
            $table->dropUnique('votes_poll_session_unique');
        });
    }
};