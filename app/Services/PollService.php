<?php

namespace App\Services;

use App\Domain\Poll\PollData;
use App\Domain\Poll\PollOptionData;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\VoteHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PollService
{
    public function getPaginatedPolls(int $userId, int $perPage = 3): LengthAwarePaginator
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
        $poll->load(['options' => fn ($q) => $q->withCount('votes')]);

        return $poll;
    }

    public function getPollWithDetails(Poll $poll): array
    {
        $poll->load(['options', 'user']);
        $poll->loadCount(['votes', 'options']);

        $voteHistory = VoteHistory::with(['fromOption', 'toOption'])
            ->where('poll_id', $poll->id)
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        return [
            'poll'        => $poll,
            'resultRows'  => $poll->resultRows(),
            'totalVotes'  => $poll->totalVotesCount(),
            'voteHistory' => $voteHistory,
        ];
    }

    public function createPoll(User $user, PollData $data): Poll
    {
        return DB::transaction(function () use ($user, $data): Poll {
            $poll = Poll::create([
                'user_id'   => $user->id,
                'question'  => $data->question,
                'is_active' => $data->isActive,
                'starts_at' => $data->startsAt,
                'ends_at'   => $data->endsAt,
            ]);

            $poll->options()->createMany(
                collect($data->options)->map(fn (PollOptionData $option, int $index) => [
                    'option_text' => $option->text,
                    'sort_order'  => $index + 1,
                    'vote_count'  => 0,
                ])->all()
            );

            return $poll;
        });
    }

    public function updatePoll(Poll $poll, PollData $data): void
    {
        DB::transaction(function () use ($poll, $data): void {
            $poll->update([
                'question'  => $data->question,
                'is_active' => $data->isActive,
                'starts_at' => $data->startsAt,
                'ends_at'   => $data->endsAt,
            ]);

            $existingOptionIds = $poll->options->pluck('id')->all();

            $keptIds = collect($data->options)
                ->filter(fn (PollOptionData $o) => $o->id !== null)
                ->map(fn (PollOptionData $o) => $o->id)
                ->all();

            $poll->options()
                ->whereNotIn('id', $keptIds)
                ->whereDoesntHave('votes')
                ->delete();

            $sortOrder = 1;

            foreach ($data->options as $optionData) {
                if ($optionData->id !== null && in_array($optionData->id, $existingOptionIds)) {
                    PollOption::where('id', $optionData->id)
                        ->where('poll_id', $poll->id)
                        ->update([
                            'option_text' => $optionData->text,
                            'is_active'   => $optionData->isActive,
                            'sort_order'  => $sortOrder,
                        ]);
                } else {
                    $poll->options()->create([
                        'option_text' => $optionData->text,
                        'is_active'   => true,
                        'sort_order'  => $sortOrder,
                        'vote_count'  => 0,
                    ]);
                }

                $sortOrder++;
            }
        });
    }
}
