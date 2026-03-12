<?php

namespace App\Services;

use App\Http\Requests\StorePollRequest;
use App\Http\Requests\UpdatePollRequest;
use App\Models\Poll;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PollService
{
    public function getPaginatedPolls(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Poll::query()
            ->where('user_id', $userId)
            ->withCount(['options', 'votes'])
            ->latest()
            ->paginate($perPage);
    }

    public function hasVotes(Poll $poll): bool
    {
        return $poll->votes()->exists();
    }

    public function getPollForEdit(Poll $poll): Poll
    {
        $poll->load('options');

        return $poll;
    }

    public function getPollWithDetails(Poll $poll): Poll
    {
        $poll->load(['options', 'user']);
        $poll->loadCount(['votes', 'options']);

        return $poll;
    }

    public function getPollWithResults(Poll $poll): array
    {
        $poll->load(['options.votes', 'user']);

        return [
            'poll'       => $poll,
            'resultRows' => $poll->resultRows(),
            'totalVotes' => $poll->totalVotesCount(),
        ];
    }

    public function createPoll(User $user, StorePollRequest $request): Poll
    {
        return DB::transaction(function () use ($user, $request): Poll {
            $poll = Poll::create([
                'user_id'   => $user->id,
                'question'  => trim($request->input('question')),
                'is_active' => (bool) $request->input('is_active'),
                'starts_at' => $request->input('starts_at'),
                'ends_at'   => $request->input('ends_at'),
            ]);

            $poll->options()->createMany(
                $request->cleanOptions()->map(fn ($option, $index) => [
                    'option_text' => $option,
                    'sort_order'  => $index + 1,
                ])->all()
            );

            return $poll;
        });
    }

    public function updatePoll(Poll $poll, UpdatePollRequest $request): void
    {
        DB::transaction(function () use ($poll, $request): void {
            $poll->update([
                'question'  => trim($request->input('question')),
                'is_active' => (bool) $request->input('is_active'),
                'starts_at' => $request->input('starts_at'),
                'ends_at'   => $request->input('ends_at'),
            ]);

            $poll->options()->delete();

            $poll->options()->createMany(
                $request->cleanOptions()->map(fn ($option, $index) => [
                    'option_text' => $option,
                    'sort_order'  => $index + 1,
                ])->all()
            );
        });
    }
}
