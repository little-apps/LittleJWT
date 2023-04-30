<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

/**
 * This exception is thrown when the JWT is tried to be verified with an incompatible hash algorithm.
 */
class IncompatibleHashAlgorithmJWK extends Exception
{
    /**
     * Base exception that caused this to be thrown.
     *
     * @var Exception
     */
    protected $baseException;

    public function __construct(Exception $e)
    {
        parent::__construct('The hash algorithm is incompatible with the provided JWK.');

        $this->baseException = $e;
    }

    /**
     * Gets base exception.
     *
     * @return Exception
     */
    public function getBaseException()
    {
        return $this->baseException;
    }
}
