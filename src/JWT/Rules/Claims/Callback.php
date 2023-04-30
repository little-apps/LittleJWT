<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JWT;

class Callback extends Rule
{
    /**
     * Callback that recieves claim value, key, and JWT.
     *
     * @var callable(mixed, string, JWT): void
     */
    protected $callback;

    /**
     * Initializes Callback rule.
     *
     * @param string $key Claim key.
     * @param callable(mixed, string, JWT): void $callback
     * @param [type] $inHeader
     */
    public function __construct($key, callable $callback, $inHeader)
    {
        parent::__construct($key, $inHeader);

        $this->callback = $callback;
    }

    /**
     * Calls callback
     *
     * @param JWT $jwt JWT instance.
     * @param mixed $value Claim value.
     * @return boolean
     */
    protected function checkClaim(JWT $jwt, $value)
    {
        return (bool) call_user_func($this->callback, $value, $this->key, $jwt);
    }
}
