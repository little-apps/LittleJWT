<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\JWT\JsonWebToken;

interface Mutator
{
    /**
     * Serializes claim value
     *
     * @param mixed $value Unserialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param JsonWebToken $jwt Original JWT
     * @return string|array|int
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt);

    /**
     * Unserializes claim value
     *
     * @param string|array|int $value Serialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param JsonWebToken $jwt Original JWT
     * @return mixed
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt);
}
