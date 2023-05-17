<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;

/**
 * Thrown when the JWTs hash algorithm is invalid.
 */
class CantResolveMutator extends Exception
{
    /**
     * The definition that couldn't be resolved.
     *
     * @var mixed
     */
    public $definition;

    public function __construct($definition) {
        parent::__construct('Unable to resolve mutator from definition.');

        $this->definition = $definition;
    }
}
