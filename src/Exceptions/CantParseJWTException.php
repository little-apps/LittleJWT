<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

/**
 * This exception is thrown when a JWT cannot be parsed.
 */
class CantParseJWTException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot parse JWT.');
    }
}
