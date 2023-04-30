<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;

class ObjectMutator implements Mutator
{
    /**
     * Serializes claim value
     *
     * @param mixed $value Unserialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return string|array|int
     */
    public function serialize($value, string $key, array $args, array $claims)
    {
        return json_encode($value);
    }

    /**
     * Unserializes claim value
     *
     * @param string|array|int $value Serialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return mixed
     */
    public function unserialize($value, string $key, array $args, array $claims)
    {
        return json_decode($value, false);
    }
}
