<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\Factories\JWTHasher;

class Sign
{
    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    /**
     * Json Web Key to sign with.
     *
     * @var JsonWebKey
     */
    protected $jwk;

    /**
     * Initializes Builder instance.
     */
    public function __construct(Application $app, JsonWebKey $jwk)
    {
        $this->app = $app;
        $this->jwk = $jwk;
    }

    /**
     * Signs a JWT
     *
     * @param JsonWebToken $jwt
     * @return SignedJsonWebToken
     */
    public function sign(JsonWebToken $jwt) {
        $algorithm = $this->jwk->algorithm();
        $signature = JWTHasher::hash($algorithm, $this->jwk, $jwt->getHeaders(), $jwt->getPayload());

        return SignedJsonWebToken::createFromJsonWebtoken($jwt, $signature);
    }
}
