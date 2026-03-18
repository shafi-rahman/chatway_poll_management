<?php

namespace App\Domain\Poll;

use App\Models\Poll;
use Illuminate\Support\Carbon;

final class PollAvailability
{
    private function __construct(
        public readonly bool $hasStarted,
        public readonly bool $hasEnded,
        public readonly bool $isAvailable,
    ) {}

    public static function for(Poll $poll): self
    {
        $now        = Carbon::now();
        $hasStarted = is_null($poll->starts_at) || $poll->starts_at->lte($now);
        $hasEnded   = ! is_null($poll->ends_at) && $poll->ends_at->lt($now);

        return new self(
            hasStarted:  $hasStarted,
            hasEnded:    $hasEnded,
            isAvailable: $poll->is_active && $hasStarted && ! $hasEnded,
        );
    }
}
