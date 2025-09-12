<?php
declare(strict_types=1);

namespace App\Application\Exceptions;

use RuntimeException;

class InvalidCredentialsException extends RuntimeException
{
    public function __construct(string $message = "Invalid email or password.")
    {
        parent::__construct($message, 401); // 401 Unauthorized
    }
}
