<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JsonWebToken;

class Callback extends Rule
{
    /**
     * Callback that recieves claim value, key, and JWT.
     *
     * @var callable(mixed, string, JsonWebToken): void
     */
    protected $callback;

    /**
     * Initializes Callback rule.
     *
     * @param string $key Claim key.
     * @param callable(mixed, string, JsonWebToken): void $callback
     * @param bool $inHeader
     */
    public function __construct($key, callable $callback, $inHeader)
    {
        parent::__construct($key, $inHeader);

        $this->callback = $callback;
    }

    /**
     * Calls callback
     *
     * @param JsonWebToken $jwt JWT instance.
     * @param mixed $value Claim value.
     * @return bool
     */
    protected function checkClaim(JsonWebToken $jwt, $value)
    {
        return (bool) call_user_func($this->callback, $value, $this->key, $jwt);
    }
}
