<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\Exceptions\RuleFailedException;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class Callback extends Rule
{
    /**
     * Callback to call
     *
     * @var callable
     */
    protected $callback;

    /**
     * Holds last rule fail message.
     *
     * @var string|null
     */
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

    /**
     * @inheritDoc
     */
    public function passes(JsonWebToken $jwt)
    {
        $this->lastMessage = null;

        try {
            return (bool) call_user_func($this->callback, $jwt);
        } catch (RuleFailedException $ex) {
            $this->lastMessage = $ex->getMessage();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return $this->lastMessage ?? 'The callback validation did not pass.';
    }
}
