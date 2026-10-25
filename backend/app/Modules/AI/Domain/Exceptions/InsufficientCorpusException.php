<?php

declare(strict_types=1);

namespace App\Modules\AI\Domain\Exceptions;

use RuntimeException;

final class InsufficientCorpusException extends RuntimeException
{
    public function __construct(string $message = 'Insufficient published corpus to answer this question.')
    {
        parent::__construct($message);
    }
}
