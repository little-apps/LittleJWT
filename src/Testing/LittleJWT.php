<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\ForwardsCalls;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\LittleJWT as RealLittleJWT;
use LittleApps\LittleJWT\Validation\Valid;

/**
 * @mixin \LittleApps\LittleJWT\LittleJWT
 */
class LittleJWT extends RealLittleJWT
{
    use ForwardsCalls;

    public function __construct(Application $app, JsonWebKey $jwk)
    {
        parent::__construct($app, $jwk);
    }

    /**
     * Handle JWTs with mutating.
     *
     * @return TestMutateHandler
     */
    public function withMutate()
    {
        return new TestMutateHandler($this->app, $this->jwk, $this->customMutatorsMapping, true);
    }

    /**
     * Handle JWTs without mutating.
     *
     * @return TestHandler
     */
    public function withoutMutate()
    {
        return new TestHandler($this->app, $this->jwk);
    }

    /**
     * Creates a Valid instance for checking if a JWT is valid.
     *
     * @param  JsonWebToken  $jwt  JWT instance to validate (generated by parseToken() method)
     * @return TestValid Valid instance (before validation is done)
     */
    public function valid(JsonWebToken $jwt)
    {
        return new TestValid($this->app, $jwt, $this->jwk);
    }
}
