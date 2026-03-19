<?php

namespace App\Services;

use App\Domain\Poll\PollAvailability;
use App\Events\PollVoteUpdated;
use App\Jobs\RecordVoteHistory;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoteService
{
    public static function pollCacheKey(Poll $poll): string
    {
        return "poll.relations.{$poll->uuid}";
    }

    public function getPollData(Poll $poll, string $cookieToken): array
    {
        $relations = Cache::remember(self::pollCacheKey($poll), now()->addHours(2), function () use ($poll) {
            $poll->load(['options', 'user']);

            return ['options' => $poll->options, 'user' => $poll->user];
        });

        $poll->setRelation('options', $relations['options']);
        $poll->setRelation('user', $relations['user']);

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
        $poll->loadMissing('options');

        $selectedOption = $poll->options->firstWhere('id', $optionId);

        if (!$selectedOption || !$selectedOption->is_active) {
            return null;
        }

        try {

            [$isUpdate, $voteId, $oldOptionId] = DB::transaction(function () use ($poll, $selectedOption, $ipAddress, $cookieToken) {

                $existingVote = Vote::where('poll_id', $poll->id)
                    ->where('session_token', $cookieToken)
                    ->lockForUpdate()
                    ->first();

                if ($existingVote !== null) {
                    $oldOptionId = $existingVote->poll_option_id;

                    if ($oldOptionId === $selectedOption->id) {
                        return [true, $existingVote->id, null];
                    }

                    $existingVote->update(['poll_option_id' => $selectedOption->id]);
                    PollOption::where('id', $oldOptionId)->decrement('vote_count');
                    PollOption::where('id', $selectedOption->id)->increment('vote_count');

                    return [true, $existingVote->id, $oldOptionId];
                }

                $vote = Vote::create([
                    'poll_id'        => $poll->id,
                    'poll_option_id' => $selectedOption->id,
                    'ip_address'     => $ipAddress,
                    'session_token'  => $cookieToken,
                ]);

                Poll::where('id', $poll->id)->increment('total_votes');
                PollOption::where('id', $selectedOption->id)->increment('vote_count');

                return [false, $vote->id, null];
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

        if (!$isUpdate || $oldOptionId !== null) {
            RecordVoteHistory::dispatch(
                $voteId,
                $poll->id,
                $oldOptionId,
                $selectedOption->id,
                $ipAddress,
                $cookieToken,
            );
        }

        if (!$isUpdate) {
            $poll->total_votes    += 1;
            $selectedOption->vote_count += 1;
        } elseif ($oldOptionId !== null) {
            $oldOption = $poll->options->firstWhere('id', $oldOptionId);
            if ($oldOption) {
                $oldOption->vote_count -= 1;
            }
            $selectedOption->vote_count += 1;
        }

        $resultRows = $poll->resultRows()->values()->all();
        $totalVotes = $poll->totalVotesCount();

        try {
            broadcast(new PollVoteUpdated($poll, $resultRows, $totalVotes));
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed.', [
                'poll_id'   => $poll->id,
                'poll_uuid' => $poll->uuid,
                'option_id' => $optionId,
                'error'     => $e->getMessage(),
            ]);
        }

        return compact('resultRows', 'totalVotes', 'isUpdate');
    }
}
