<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

class IncompatibleHashAlgorithmJWK extends Exception
{
    protected $baseException;

    public function __construct(Exception $e)
    {
        parent::__construct('The hash algorithm is incompatible with the provided JWK.');

        $this->baseException = $e;
    }

    public function getBaseException() {
        return $this->baseException;
    }
}
