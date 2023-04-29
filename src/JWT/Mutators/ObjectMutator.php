<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;

class ObjectMutator implements Mutator
{
    /**
     * Serializes claim value
     *
     * @param  mixed  $value Claim value
     * @param  string $key   Claim key
     * @param  array  $args  Any arguments to use for mutation
     * @return mixed
     */
    public function serialize($value, string $key, array $args)
    {
        return json_encode($value);
    }

    /**
     * Unserializes claim value
     *
     * @param  mixed  $value Claim value
     * @param  string $key   Claim key
     * @param  array  $args  Any arguments to use for mutation
     * @return mixed
     */
    public function unserialize($value, string $key, array $args)
    {
        return json_decode($value, false);
    }
}
