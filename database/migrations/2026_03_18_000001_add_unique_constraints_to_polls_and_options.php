<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->unique(['user_id', 'question']);
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->unique(['poll_id', 'option_text']);
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'question']);
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'option_text']);
        });
    }
};
