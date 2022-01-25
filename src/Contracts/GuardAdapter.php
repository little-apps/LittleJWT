<?php

namespace LittleApps\LittleJWT\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

use LittleApps\LittleJWT\JWT\JWT;

interface GuardAdapter
{
    /**
     * Parse a token from a string to a JWT.
     * This does NOT check if the JWT is valid.
     *
     * @param string $token
     * @return JWT JWT instance or null if unable to be parsed.
     */
    public function parseToken(string $token);

    /**
     * Runs JWT through Validator.
     *
     * @param JWT $jwt
     * @return bool True if JWT is validated.
     */
    public function validateJwt(JWT $jwt);

    /**
     * Gets a user from the JWT
     *
     * @param UserProvider $provider
     * @param JWT $jwt
     * @return Authenticatable
     */
    public function getUserFromJwt(UserProvider $provider, JWT $jwt);
}
