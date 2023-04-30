<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\Contracts\Rule as RuleContract;
use LittleApps\LittleJWT\JWT\JWT;

abstract class Rule implements RuleContract
{
    /**
     * @inheritDoc
     */
    abstract public function passes(JWT $jwt);

    /**
     * @inheritDoc
     */
    abstract public function message();

    /**
     * @inheritDoc
     */
    public function getKey()
    {
        return get_class($this);
    }
}
