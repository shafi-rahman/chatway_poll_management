<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uuid',
        'question',
        'is_active',
        'total_votes',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_active' => 'boolean',
            'total_votes' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Poll $poll): void {
            if (empty($poll->uuid)) {
                $poll->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function resultRows(): Collection
    {
        $this->loadMissing('options');

        $totalVotes = (int) $this->total_votes;

        return $this->options->map(function ($option) use ($totalVotes) {
            $voteCount = (int) $option->vote_count;
            $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0;

            return [
                'id' => $option->id,
                'option_text' => $option->option_text,
                'vote_count' => $voteCount,
                'percentage' => $percentage,
            ];
        });
    }

    public function totalVotesCount(): int
    {
        return (int) $this->total_votes;
    }
}