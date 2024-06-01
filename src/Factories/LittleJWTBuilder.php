<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\LittleJWT;
use LittleApps\LittleJWT\JWK\JWKValidator;

class LittleJWTBuilder
{
    /**
     * Creates LittleJWT instance.
     *
     * @param  Application  $app  Application container
     * @param  JsonWebKey  $jwk  JWK to sign and verify JWTs with.
     * @param boolean $validateJwk If true, validates JWK (default: true)
     * @return static
     */
    public static function create(Application $app, JsonWebKey $jwk, bool $validateJwk = true): LittleJWT {
        if ($validateJwk)
            JWKValidator::validate($jwk);

        return new LittleJWT($app, $jwk);
    }
}
