<?php

namespace App\Services;

use App\Events\PollVoteUpdated;
use App\Models\Poll;
use App\Models\Vote;
use App\Models\PollOption;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function hasAlreadyVoted(Poll $poll, string $cookieToken): bool
    {
        return Vote::query()
            ->where('poll_id', $poll->id)
            ->where('session_token', $cookieToken)
            ->exists();
    }

    public function getPollData(Poll $poll, string $cookieToken): array
    {
        $poll->load(['options', 'user']);

        [$hasStarted, $hasEnded, $isAvailableForVoting] = $this->getPollAvailability($poll);

        $hasAlreadyVoted = $this->hasAlreadyVoted($poll, $cookieToken);

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

    // Returns ['resultRows' => array, 'totalVotes' => int], false on duplicate/db error, null if option invalid
    public function submitVote(Poll $poll, int $optionId, string $ipAddress, string $cookieToken): array|false|null
    {
        $selectedOption = $poll->options()->where('id', $optionId)->first();

        if (!$selectedOption) {
            return null;
        }

        try {
            DB::transaction(function () use ($poll, $selectedOption, $ipAddress, $cookieToken): void {
                Vote::create([
                    'poll_id'        => $poll->id,
                    'poll_option_id' => $selectedOption->id,
                    'ip_address'     => $ipAddress,
                    'session_token'  => $cookieToken,
                ]);

                Poll::query()
                    ->where('id', $poll->id)
                    ->increment('total_votes');

                PollOption::query()
                    ->where('id', $selectedOption->id)
                    ->increment('vote_count');
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                Log::warning('Duplicate vote attempt blocked at DB level.', [
                    'poll_id'      => $poll->id,
                    'poll_uuid'    => $poll->uuid,
                    'option_id'    => $optionId,
                    'ip_address'   => $ipAddress,
                    'cookie_token' => $cookieToken,
                ]);

                return false;
            }

            Log::error('Vote submission failed due to unexpected database error.', [
                'poll_id'      => $poll->id,
                'poll_uuid'    => $poll->uuid,
                'option_id'    => $optionId,
                'ip_address'   => $ipAddress,
                'cookie_token' => $cookieToken,
                'error'        => $e->getMessage(),
            ]);

            throw $e;
        }

        $poll->refresh()->load('options');

        $resultRows = $poll->resultRows()->values()->all();
        $totalVotes = $poll->totalVotesCount();

        try {
            broadcast(new PollVoteUpdated($poll, $resultRows, $totalVotes));
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed.', [
                'poll_id' => $poll->id,
                'poll_uuid' => $poll->uuid,
                'option_id' => $optionId,
                'error' => $e->getMessage(),
            ]);
        }

        return compact('resultRows', 'totalVotes');
    }
}
