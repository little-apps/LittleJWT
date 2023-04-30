<?php

namespace LittleApps\LittleJWT\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when the JWT can't be verified.
 */
class InvalidTokenException extends HttpException
{
    public function __construct($message = null)
    {
        parent::__construct(401, $message ?? 'The provided JWT is invalid.');
    }
}
