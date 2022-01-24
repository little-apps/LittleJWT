<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\JWT\JWT;

interface BlacklistDriver
{
    /**
     * Checks if JWT is blacklisted.
     *
     * @param JWT $jwt
     * @return bool True if blacklisted.
     */
    public function isBlacklisted(JWT $jwt);

    /**
     * Blacklists a JWT.
     *
     * @param JWT $jwt
     * @param int $ttl Length of time (in seconds) a JWT is blacklisted (0 means forever). If negative, the default TTL is used. (default: -1)
     * @return $this
     */
    public function blacklist(JWT $jwt, $ttl = -1);

    /**
     * Cleanup blacklist.
     *
     * @return $this
     */
    public function purge();
}
