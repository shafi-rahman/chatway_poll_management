<?php

namespace App\Domain\Poll;

final class PollOptionData
{
    public function __construct(
        public readonly string $text,
        public readonly bool   $isActive = true,
        public readonly ?int   $id       = null,
    ) {}
}
