<?php

namespace LittleApps\LittleJWT\Build;

use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;

class Sign
{
    /**
     * Json Web Key to sign with.
     *
     * @var JsonWebKey
     */
    protected $jwk;

    /**
     * Initializes Sign instance.
     */
    public function __construct(JsonWebKey $jwk)
    {
        $this->jwk = $jwk;
    }

    /**
     * Signs a JWT
     *
     * @return SignedJsonWebToken
     */
    public function sign(JsonWebToken $jwt)
    {
        $algorithm = $this->jwk->algorithm();
        $signature = JWTHasher::hash($algorithm, $this->jwk, $jwt->getHeaders(), $jwt->getPayload());

        return SignedJsonWebToken::instance($jwt, $signature);
    }
}
