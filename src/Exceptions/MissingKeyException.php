<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

class MissingKeyException extends Exception
{
    public function __construct()
    {
        parent::__construct('The secret for Little JWT is missing.');
    }
}
