<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Domain\Exceptions;

use RuntimeException;

final class AccountLockedException extends RuntimeException {}
