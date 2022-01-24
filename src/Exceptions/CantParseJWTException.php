<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

class CantParseJWTException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot parse JWT.');
    }
}
