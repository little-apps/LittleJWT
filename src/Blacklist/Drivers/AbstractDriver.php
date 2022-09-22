<?php

namespace LittleApps\LittleJWT\Blacklist\Drivers;

use LittleApps\LittleJWT\Contracts\BlacklistDriver;
use LittleApps\LittleJWT\JWT\JWT;

abstract class AbstractDriver implements BlacklistDriver
{
    /**
     * Gets a unique identifier for the JWT
     *
     * @param JWT $jwt
     * @return string
     */
    protected function getUniqueId(JWT $jwt)
    {
        // Use jti claim (if it exists)
        if ($jwt->getPayload()->has('jti')) {
            return (string) $jwt->getPayload()->get('jti');
        }

        // Otherwise, use sha1 of JWT token.
        return sha1((string) $jwt);
    }

    /**
     * Gets default TTL to use for blacklist
     *
     * @return int TTL in seconds (0 means forever)
     */
    protected function getDefaultTtl()
    {
        return config('littlejwt.blacklist.ttl', 0);
    }
}
