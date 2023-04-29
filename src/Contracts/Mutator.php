<?php

namespace LittleApps\LittleJWT\Contracts;

interface Mutator
{
    /**
     * Serializes claim value
     *
     * @param mixed $value Unserialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @return string|array|int
     */
    public function serialize($value, string $key, array $args);

    /**
     * Unserializes claim value
     *
     * @param string|array|int $value Serialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @return mixed
     */
    public function unserialize($value, string $key, array $args);
}
