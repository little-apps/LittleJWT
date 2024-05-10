<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class JsonMutator implements Mutator
{
    /**
     * {@inheritDoc}
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return json_encode($value);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return json_decode($value, true);
    }
}
