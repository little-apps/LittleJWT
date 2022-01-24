<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\Contracts\Rule as RuleContract;
use LittleApps\LittleJWT\JWT\JWT;

abstract class Rule implements RuleContract
{
    /**
     * Checks if JWT passes rule.
     *
     * @param \LittleApps\LittleJWT\JWT\JWT $jwt
     * @return bool True if JWT passes rule check.
     */
    abstract public function passes(JWT $jwt);

    /**
     * Gets the error message for when the rule fails.
     *
     * @return string
     */
    abstract public function message();

    /**
     * Gets the key to be used for the error messages.
     *
     * @return string
     */
    public function getKey()
    {
        return get_class($this);
    }
}
