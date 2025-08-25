<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

/**
 * Base class for all domain-level exceptions.
 * 
 * Extend this in your specific exceptions (e.g., InvalidAddressException).
 * 
 * Having a common base makes it easier for the application layer
 * (middleware/exception handler) to catch and map domain errors into
 * standardized API responses.
 */
abstract class DomainBaseException extends \DomainException
{
    protected int $statusCode;

    public function __construct(string $message, int $statusCode = 400, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }
    
    /**
     * Get the recommended HTTP status code for this exception.
     * Default: 400 Bad Request.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Convert exception details into a JSON-ready array.
     */
    public function toArray(): array
    {
        return [
            'error' => [
                'type'    => static::class,
                'message' => $this->getMessage(),
            ]
        ];
    }
}
