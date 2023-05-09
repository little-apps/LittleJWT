<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\JWT\JsonWebToken;

interface Rule
{
    /**
     * Checks if JWT passes rule.
     *
     * @param \LittleApps\LittleJWT\JWT\JsonWebToken $jwt
     * @return bool True if JWT passes rule check.
     */
    public function passes(JsonWebToken $jwt);

    /**
     * Gets the error message for when the rule fails.
     *
     * @return string
     */
    public function message();

    /**
     * Gets the key to be used for the error messages.
     *
     * @return string|null
     */
    public function getKey();
}
