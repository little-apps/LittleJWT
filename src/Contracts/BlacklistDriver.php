<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\JWT\JsonWebToken;

interface BlacklistDriver
{
    /**
     * Checks if JWT is blacklisted.
     *
     * @return bool True if blacklisted.
     */
    public function isBlacklisted(JsonWebToken $jwt);

    /**
     * Blacklists a JWT.
     *
     * @param  int  $ttl  Length of time (in seconds) a JWT is blacklisted (0 means forever). If negative, the default TTL is used. (default: -1)
     * @return $this
     */
    public function blacklist(JsonWebToken $jwt, $ttl = -1);

    /**
     * Cleanup blacklist.
     *
     * @return $this
     */
    public function purge();
}
