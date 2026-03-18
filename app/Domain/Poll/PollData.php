<?php

namespace App\Domain\Poll;

use App\Exceptions\PollDomainException;
use Illuminate\Support\Carbon;

final class PollData
{
    private function __construct(
        public readonly string  $question,
        public readonly bool    $isActive,
        public readonly ?Carbon $startsAt,
        public readonly ?Carbon $endsAt,
        public readonly array   $options,
    ) {}

    // build PollData from a poll creation payload
    public static function forCreate(array $validated): self
    {
        $question = trim($validated['question'] ?? '');

        if ($question === '') {
            throw new PollDomainException('Poll question cannot be empty.', 'question');
        }

        $isActive = (bool) ($validated['is_active'] ?? false);
        $startsAt = isset($validated['starts_at']) ? Carbon::parse($validated['starts_at']) : null;
        $endsAt   = isset($validated['ends_at'])   ? Carbon::parse($validated['ends_at'])   : null;

        self::assertActivePollHasDates($isActive, $startsAt, $endsAt);
        self::assertDateRange($startsAt, $endsAt);

        $options = collect($validated['options'] ?? [])
            ->map(fn ($text) => is_string($text) ? trim($text) : '')
            ->filter(fn ($text) => $text !== '')
            ->unique()
            ->values()
            ->map(fn (string $text) => new PollOptionData(text: $text, isActive: true))
            ->all();

        if ($isActive && count($options) < 2) {
            throw new PollDomainException('Please provide at least two valid poll options.', 'options');
        }

        return new self(
            question: $question,
            isActive: $isActive,
            startsAt: $startsAt,
            endsAt:   $endsAt,
            options:  $options,
        );
    }

    // build PollData from a poll update payload
    public static function forUpdate(array $validated): self
    {
        $question = trim($validated['question'] ?? '');

        if ($question === '') {
            throw new PollDomainException('Poll question cannot be empty.', 'question');
        }

        $isActive = (bool) ($validated['is_active'] ?? false);
        $startsAt = isset($validated['starts_at']) ? Carbon::parse($validated['starts_at']) : null;
        $endsAt   = isset($validated['ends_at'])   ? Carbon::parse($validated['ends_at'])   : null;

        self::assertActivePollHasDates($isActive, $startsAt, $endsAt);
        self::assertDateRange($startsAt, $endsAt);

        $options = collect($validated['options'] ?? [])
            ->filter(fn ($o) => filled(trim($o['text'] ?? '')))
            ->map(fn ($o) => new PollOptionData(
                text:     trim($o['text']),
                isActive: isset($o['is_active']) && (bool) $o['is_active'],
                id:       !empty($o['id']) ? (int) $o['id'] : null,
            ))
            ->values()
            ->all();

        if ($isActive && count($options) < 2) {
            throw new PollDomainException('Please provide at least two valid poll options.', 'options');
        }

        if ($isActive) {
            $activeCount = collect($options)->filter(fn (PollOptionData $o) => $o->isActive)->count();

            if ($activeCount < 2) {
                throw new PollDomainException('A poll must have at least two active options.', 'options');
            }
        }

        return new self(
            question: $question,
            isActive: $isActive,
            startsAt: $startsAt,
            endsAt:   $endsAt,
            options:  $options,
        );
    }

    private static function assertActivePollHasDates(bool $isActive, ?Carbon $startsAt, ?Carbon $endsAt): void
    {
        if (! $isActive) {
            return;
        }

        if ($startsAt === null) {
            throw new PollDomainException('An active poll must have a start date.', 'starts_at');
        }

        if ($endsAt === null) {
            throw new PollDomainException('An active poll must have an end date.', 'ends_at');
        }

        if ($endsAt->isPast()) {
            throw new PollDomainException('The end date must be in the future for an active poll.', 'ends_at');
        }
    }

    private static function assertDateRange(?Carbon $startsAt, ?Carbon $endsAt): void
    {
        if ($startsAt !== null && $endsAt !== null && ! $endsAt->isAfter($startsAt)) {
            throw new PollDomainException('The end date must be after the start date.', 'ends_at');
        }
    }
}
