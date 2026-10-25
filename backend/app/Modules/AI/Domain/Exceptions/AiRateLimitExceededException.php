<?php

declare(strict_types=1);

namespace App\Modules\AI\Domain\Exceptions;

use RuntimeException;

final class AiRateLimitExceededException extends RuntimeException
{
    public function __construct(string $message = 'AI rate limit exceeded.')
    {
        parent::__construct($message);
    }
}
