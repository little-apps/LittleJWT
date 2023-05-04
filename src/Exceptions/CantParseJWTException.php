<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;
use Throwable;

/**
 * This exception is thrown when a JWT cannot be parsed.
 */
class CantParseJWTException extends Exception
{
    /**
     * Inner exception
     *
     * @var \Throwable|null
     */
    public $inner;

    public function __construct(Throwable $inner = null)
    {
        parent::__construct('Cannot parse JWT.');

        $this->inner = $inner;
    }
}
