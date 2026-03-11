<?php

namespace App\Events;

use App\Models\Poll;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PollVoteUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    // create a new event instance
    public function __construct(
        public Poll $poll,
        public array $resultRows,
        public int $totalVotes
    ) {}

    // channels the event should broadcast on
    public function broadcastOn(): array
    {
        return [
            new Channel('poll.' . $this->poll->uuid),
        ];
    }

    // ustom event name for Echo listeners
    public function broadcastAs(): string
    {
        return 'poll.vote.updated';
    }

    // sent to clients
    public function broadcastWith(): array
    {
        return [
            'poll_uuid' => $this->poll->uuid,
            'total_votes' => $this->totalVotes,
            'result_rows' => $this->resultRows,
        ];
    }
}