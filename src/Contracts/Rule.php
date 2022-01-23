<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\JWT\JWT;

interface Rule {
    /**
     * Checks if JWT passes rule.
     *
     * @param \LittleApps\LittleJWT\JWT\JWT $jwt
     * @return bool True if JWT passes rule check.
     */
    public function passes(JWT $jwt);

    /**
     * Gets the error message for when the rule fails.
     *
     * @return string
     */
    public function message();

    /**
     * Gets the key to be used for the error messages.
     *
     * @return string
     */
    public function getKey();
}
