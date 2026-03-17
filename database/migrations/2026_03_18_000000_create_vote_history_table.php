<?php

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_history', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vote::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Poll::class)->constrained()->cascadeOnDelete();
            $table->foreignId('from_option_id')->nullable()->constrained('poll_options')->nullOnDelete();
            $table->foreignId('to_option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('session_token');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_history');
    }
};
