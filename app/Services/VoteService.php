<?php

namespace App\Services;

use App\Events\PollVoteUpdated;
use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoteService
{
    public function getPollAvailability(Poll $poll): array
    {
        $now = Carbon::now();
        $hasStarted            = is_null($poll->starts_at) || $poll->starts_at->lte($now);
        $hasEnded              = !is_null($poll->ends_at) && $poll->ends_at->lt($now);
        $isAvailableForVoting  = $poll->is_active && $hasStarted && !$hasEnded;

        return [$hasStarted, $hasEnded, $isAvailableForVoting];
    }

    public function ensureSessionToken(Request $request): string
    {
        if (!$request->session()->has('poll_voter_token')) {
            $request->session()->put('poll_voter_token', (string) Str::uuid());
        }

        return (string) $request->session()->get('poll_voter_token');
    }

    public function hasAlreadyVoted(Poll $poll, string $ipAddress, string $sessionToken): bool
    {
        return Vote::query()
            ->where('poll_id', $poll->id)
            ->where(function ($query) use ($ipAddress, $sessionToken) {
                $query->where('ip_address', $ipAddress)
                      ->orWhere('session_token', $sessionToken);
            })
            ->exists();
    }

    public function getPollData(Poll $poll, Request $request): array
    {
        $poll->load(['options', 'user']);

        [$hasStarted, $hasEnded, $isAvailableForVoting] = $this->getPollAvailability($poll);

        $sessionToken    = $this->ensureSessionToken($request);
        $hasAlreadyVoted = $this->hasAlreadyVoted($poll, $request->ip(), $sessionToken);

        return [
            'poll'                 => $poll,
            'hasStarted'           => $hasStarted,
            'hasEnded'             => $hasEnded,
            'isAvailableForVoting' => $isAvailableForVoting,
            'hasAlreadyVoted'      => $hasAlreadyVoted,
            'resultRows'           => $poll->resultRows(),
            'totalVotes'           => $poll->totalVotesCount(),
        ];
    }

    // Returns ['resultRows' => array, 'totalVotes' => int] or null if option invalid
    public function submitVote(Poll $poll, int $optionId, string $ipAddress, string $sessionToken): ?array
    {
        $selectedOption = $poll->options()->where('id', $optionId)->first();

        if (!$selectedOption) {
            return null;
        }

        DB::transaction(function () use ($poll, $selectedOption, $ipAddress, $sessionToken): void {
            Vote::create([
                'poll_id'        => $poll->id,
                'poll_option_id' => $selectedOption->id,
                'ip_address'     => $ipAddress,
                'session_token'  => $sessionToken,
            ]);
        });

        $poll->loadMissing('options');
        $resultRows = $poll->resultRows()->values()->all();
        $totalVotes = $poll->totalVotesCount();

        try {
            broadcast(new PollVoteUpdated($poll, $resultRows, $totalVotes));
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed: ' . $e->getMessage());
        }

        return compact('resultRows', 'totalVotes');
    }
}
