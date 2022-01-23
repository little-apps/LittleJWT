<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Contracts\Foundation\Application;

use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\JWT\ClaimManager;

use Jose\Component\Core\JWK;

use Jose\Component\Signature\Algorithm\MacAlgorithm;

class JWTHasher {
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Checks if JWT signature matches with JWK
     *
     * @param JWK $jwk JWK to use for verification
     * @param JWT $jwt JWT to test.
     * @return bool True if JWT signature is valid.
     */
    public function verify(JWK $jwk, JWT $jwt) {
        $algorithm = $this->app->make('littlejwt.algorithm');

        if (!($algorithm instanceof MacAlgorithm))
            return false;

        $expected = $jwt->getSignature();
        $input = $this->createInput($jwt->getHeaders(), $jwt->getPayload());

        return $algorithm->verify($jwk, $input, $expected);
    }

    /**
     * Generats a hash to be used as the signature for the JWT.
     *
     * @param JWK $jwk JWK to use to create signature.
     * @param JWT $jwt JWT to create signature for.
     * @return string
     */
    public function hash(JWK $jwk, ClaimManager $headers, ClaimManager $payload) {
        $algorithm = $this->app->make('littlejwt.algorithm');

        if (!($algorithm instanceof MacAlgorithm))
            return false;

        return $algorithm->hash($jwk, $this->createInput($headers, $payload));
    }

    /**
     * Creates the input that will be sent to the HMAC hashing function.
     *
     * @param ClaimManager $headers
     * @param ClaimManager $payload
     * @return string
     */
    protected function createInput(ClaimManager $headers, ClaimManager $payload) {
        return sprintf('%s.%s', (string) $headers, (string) $payload);
    }
}
