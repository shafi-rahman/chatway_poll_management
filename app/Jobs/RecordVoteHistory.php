<?php

namespace App\Jobs;

use App\Models\VoteHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordVoteHistory implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly int     $voteId,
        private readonly int     $pollId,
        private readonly ?int    $fromOptionId,
        private readonly int     $toOptionId,
        private readonly string  $ipAddress,
        private readonly string  $sessionToken,
    ) {}

    public function handle(): void
    {
        VoteHistory::create([
            'vote_id'        => $this->voteId,
            'poll_id'        => $this->pollId,
            'from_option_id' => $this->fromOptionId,
            'to_option_id'   => $this->toOptionId,
            'ip_address'     => $this->ipAddress,
            'session_token'  => $this->sessionToken,
        ]);
    }
}
