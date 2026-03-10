<?php

use App\Models\Poll;
use App\Models\PollOption;
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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Poll::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PollOption::class)->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('session_token')->nullable();
            $table->timestamps();

            $table->index('poll_id');
            $table->index('poll_option_id');
            $table->index(['poll_id', 'ip_address']);
            $table->index(['poll_id', 'session_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};