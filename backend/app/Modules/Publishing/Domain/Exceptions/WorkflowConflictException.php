<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Domain\Exceptions;

use RuntimeException;

final class WorkflowConflictException extends RuntimeException {}
