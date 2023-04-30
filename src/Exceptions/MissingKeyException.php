<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

/**
 * Thrown when no secret for the JWK is set.
 */
class MissingKeyException extends Exception
{
    public function __construct()
    {
        parent::__construct('The secret for Little JWT is missing.');
    }
}
