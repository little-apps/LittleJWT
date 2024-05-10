<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

/**
 * Thrown when a claim value cannot be encoded.
 */
class InvalidClaimValueException extends Exception
{
    /**
     * Data that was trying to be encoded.
     *
     * @var mixed
     */
    protected $data;

    public function __construct($data, ?Exception $previous = null)
    {
        parent::__construct('One (or more) claims has a value that cannot be encoded.', 0, $previous);

        $this->data = $data;
    }

    /**
     * Gets the claim data that caused this exception.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
