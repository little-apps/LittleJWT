<?php

namespace LittleApps\LittleJWT\Testing;

use Closure;

use LittleApps\LittleJWT\LittleJWT;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Verify\Verify;
use LittleApps\LittleJWT\Verify\Verifier;
use LittleApps\LittleJWT\Testing\TestVerifier;
use LittleApps\LittleJWT\Verify\Verifiers\StackVerifier;

use Jose\Component\Core\JWK;

use Illuminate\Contracts\Foundation\Application;

class LittleJWTFake extends LittleJWT {
    protected $app;
    protected $jwk;

    public function __construct(Application $app, JWK $jwk) {
        parent::__construct($app, $jwk);
    }

    /**
     * Creates a Verify instance for checking if a JWT is valid.
     * The default callback is not added when testing.
     *
     * @param JWT $jwt
     * @param callable|Verifiable $callback Callback or Verifiable that recieves Verifier to set checks for JWT.
     * @return Verify Verify instance (before verification is done)
     */
    public function verifyJWT(JWT $jwt, $callback = null, $applyDefault = false) {
        $callbacks = [];

        // Default callback is not added because it expects Verifier and not TestVerifier
        if (!is_null($callback))
            array_push($callbacks, $callback);

        $transformCallbacks = $this->createTransformCallback($callbacks);

        $verifier = new StackVerifier([$transformCallbacks]);

        $verify = new Verify($this->app, $jwt, $this->jwk, $verifier);

        return $verify->verify();
    }

    /**
     * Creates the callback which transforms a Verifier to TestVerifier and sends it through the callbacks.
     *
     * @param iterable $callbacks
     * @return \Closure
     */
    protected function createTransformCallback(iterable $callbacks) {
        return function (Verifier $verifier) use($callbacks) {
            $testVerifier = new TestVerifier($this->app, $verifier);

            foreach ($callbacks as $callback) {
                $callback($testVerifier);
            }
        };
    }
}
