<?php

namespace LittleApps\LittleJWT\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use LittleApps\LittleJWT\JWT\JsonWebToken;

interface GuardAdapter
{
    /**
     * Parse a token from a string to a JWT.
     * This does NOT check if the JWT is valid.
     *
     * @param string $token
     * @return JsonWebToken|null JWT instance or null if unable to be parsed.
     */
    public function parse(string $token);

    /**
     * Validate the JWT.
     *
     * @param JsonWebToken $jwt
     * @return bool True if JWT is validated.
     */
    public function validate(JsonWebToken $jwt);

    /**
     * Gets a user from the JWT
     *
     * @param UserProvider $provider
     * @param JsonWebToken $jwt
     * @return Authenticatable
     */
    public function getUserFromJwt(UserProvider $provider, JsonWebToken $jwt);
}
