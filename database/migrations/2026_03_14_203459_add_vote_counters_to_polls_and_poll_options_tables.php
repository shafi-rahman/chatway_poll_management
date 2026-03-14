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
        Schema::table('polls', function (Blueprint $table) {
            $table->unsignedBigInteger('total_votes')->default(0)->after('is_active');
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->unsignedBigInteger('vote_count')->default(0)->after('option_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn('total_votes');
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->dropColumn('vote_count');
        });
    }
};