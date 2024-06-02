<?php

namespace LittleApps\LittleJWT\Validation;

use Illuminate\Support\Traits\ForwardsCalls;
use LittleApps\LittleJWT\JWT\JsonWebToken;

/**
 * Represents the result of a JWT validation.
 */
class ValidatedJsonWebToken
{
    use ForwardsCalls;

    /**
     * JWT that was validated.
     */
    protected readonly JsonWebToken $jwt;

    /**
     * The result of the validation.
     */
    protected readonly bool $result;

    /**
     * Initializes instance.
     */
    public function __construct(JsonWebToken $jwt, bool $result)
    {
        $this->jwt = $jwt;
        $this->result = $result;
    }

    /**
     * Gets the validated JWT.
     *
     * @return JsonWebToken
     */
    public function getJWT()
    {
        return $this->jwt;
    }

    /**
     * Checks if validation passed.
     *
     * @return bool
     */
    public function passes()
    {
        return $this->result;
    }

    /**
     * Checks if validation failed.
     *
     * @return bool
     */
    public function fails()
    {
        return ! $this->result;
    }

    /**
     * Forwards calls to JWT instance.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->forwardCallTo($this->getJWT(), $name, $arguments);
    }

    /**
     * Encodes JWT to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->jwt;
    }
}
