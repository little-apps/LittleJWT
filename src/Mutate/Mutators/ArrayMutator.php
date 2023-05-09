<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class ArrayMutator implements Mutator
{
    /**
     * @inheritDoc
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return (array) $value;
    }
}
