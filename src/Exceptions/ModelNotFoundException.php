<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;
use LittleApps\LittleJWT\JWT\JsonWebToken;

/**
 * This exception is thrown when the model for a JWT claim cannot be found.
 */
class ModelNotFoundException extends Exception
{
    /**
     * The model class that wasn't found.
     *
     * @var class-string
     */
    public readonly string $model;

    /**
     * The claim being referenced.
     */
    public readonly string $claim;

    /**
     * The key (claim value) that wasn't found.
     */
    public readonly mixed $key;

    /**
     * Original JWT
     *
     * @var ?JsonWebToken
     */
    public readonly ?JsonWebToken $jwt;

    public function __construct(string $model, string $claim, $key, ?JsonWebToken $jwt = null)
    {
        parent::__construct('Model could not be found from JWT claim.');

        $this->model = $model;
        $this->claim = $claim;
        $this->key = $key;
        $this->jwt = $jwt;
    }
}
