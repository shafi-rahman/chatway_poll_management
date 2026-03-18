<?php

namespace App\Services;

use App\Domain\Poll\PollAvailability;
use App\Events\PollVoteUpdated;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use App\Models\VoteHistory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoteService
{
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

        $availability = PollAvailability::for($poll);

        $currentVote = Vote::where('poll_id', $poll->id)
            ->where('session_token', $cookieToken)
            ->first();

        return [
            'poll'                 => $poll,
            'hasStarted'           => $availability->hasStarted,
            'hasEnded'             => $availability->hasEnded,
            'isAvailableForVoting' => $availability->isAvailable,
            'hasAlreadyVoted'      => $currentVote !== null,
            'currentVoteOptionId'  => $currentVote?->poll_option_id,
            'resultRows'           => $poll->resultRows(),
            'totalVotes'           => $poll->totalVotesCount(),
        ];
    }

    public function submitVote(Poll $poll, int $optionId, string $ipAddress, string $cookieToken): array|false|null
    {
        $selectedOption = $poll->options()
            ->where('id', $optionId)
            ->where('is_active', true)
            ->first();

        if (!$selectedOption) {
            return null;
        }

        $existingVote = Vote::where('poll_id', $poll->id)
            ->where('session_token', $cookieToken)
            ->first();

        $isUpdate = $existingVote !== null;

        try {
            if ($isUpdate) {
                $this->updateVote($poll, $existingVote, $selectedOption, $ipAddress, $cookieToken);
            } else {
                $this->castVote($poll, $selectedOption, $ipAddress, $cookieToken);
            }
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
                'poll_id'  => $poll->id,
                'poll_uuid' => $poll->uuid,
                'option_id' => $optionId,
                'error'    => $e->getMessage(),
            ]);
        }

        return compact('resultRows', 'totalVotes', 'isUpdate');
    }

    private function castVote(Poll $poll, PollOption $option, string $ipAddress, string $cookieToken): void
    {
        DB::transaction(function () use ($poll, $option, $ipAddress, $cookieToken): void {
            $vote = Vote::create([
                'poll_id'        => $poll->id,
                'poll_option_id' => $option->id,
                'ip_address'     => $ipAddress,
                'session_token'  => $cookieToken,
            ]);

            Poll::where('id', $poll->id)->increment('total_votes');
            PollOption::where('id', $option->id)->increment('vote_count');

            VoteHistory::create([
                'vote_id'        => $vote->id,
                'poll_id'        => $poll->id,
                'from_option_id' => null,
                'to_option_id'   => $option->id,
                'ip_address'     => $ipAddress,
                'session_token'  => $cookieToken,
            ]);
        });
    }

    private function updateVote(Poll $poll, Vote $existingVote, PollOption $newOption, string $ipAddress, string $cookieToken): void
    {
        DB::transaction(function () use ($poll, $existingVote, $newOption, $ipAddress, $cookieToken): void {
            $oldOptionId = $existingVote->poll_option_id;

            if ($oldOptionId === $newOption->id) {
                return;
            }

            VoteHistory::create([
                'vote_id'        => $existingVote->id,
                'poll_id'        => $poll->id,
                'from_option_id' => $oldOptionId,
                'to_option_id'   => $newOption->id,
                'ip_address'     => $ipAddress,
                'session_token'  => $cookieToken,
            ]);

            $existingVote->update(['poll_option_id' => $newOption->id]);

            PollOption::where('id', $oldOptionId)->decrement('vote_count');
            PollOption::where('id', $newOption->id)->increment('vote_count');
        });
    }
}
