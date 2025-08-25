<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use App\Domain\Shared\Exceptions\DomainBaseException;

final class InvalidAddressException extends DomainBaseException
{
    public function __construct(string $message = "Invalid address.")
    {
        parent::__construct($message, 422); // 422 Unprocessable Entity
    }
}
