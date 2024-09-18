<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when no secret for the JWK is set.
 */
class MissingKeyException extends Exception
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? 'The secret for Little JWT is missing.', previous: $previous);
    }
}
