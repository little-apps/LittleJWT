<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Exceptions\CantParseJWTException;

trait HandlesParse {
    /**
     * Parses and builds a JWT instance from a string.
     * This does NOT check that the JWT is valid.
     *
     * @param string $token Token to parse
     * @param bool $throw If true, CantParseJWTException is thrown instead of returning null. (default: false)
     * @return \LittleApps\LittleJWT\JWT\JsonWebToken|null Returns JWT or null if token cannot be parsed.
     */
    public function parse(string $token, bool $throw = false)
    {
        try {
            return $this->createJWTBuilder()->buildFromExisting($token);
        } catch (CantParseJWTException $ex) {
            if ($throw) {
                throw $ex;
            }

            return null;
        }
    }
}
