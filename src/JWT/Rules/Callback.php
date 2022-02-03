<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\Exceptions\RuleFailedException;

use LittleApps\LittleJWT\JWT\JWT;

class Callback extends Rule
{
    protected $callback;

    protected $lastMessage;

    /**
     * Constructor for Callback rule.
     *
     * @param callable $callback Callback that recieves the JWT and returns true/false or throws a RuleFailedException.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function passes(JWT $jwt)
    {
        try {
            return (bool) call_user_func($this->callback, $jwt);
        } catch (RuleFailedException $ex) {
            $this->lastMessage = $ex->getMessage();
        }

        return false;
    }

    public function message()
    {
        return $this->lastMessage ?? 'The callback validation did not pass.';
    }
}
