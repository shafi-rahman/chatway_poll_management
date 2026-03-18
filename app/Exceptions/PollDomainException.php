<?php

namespace App\Exceptions;

use RuntimeException;

final class PollDomainException extends RuntimeException
{
    public function __construct(string $message, public readonly string $field = 'options')
    {
        parent::__construct($message);
    }
}
