<?php

namespace LittleApps\LittleJWT;

use Closure;

use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Build\Build;
use LittleApps\LittleJWT\Build\Builders\DefaultBuilder;
use LittleApps\LittleJWT\Contracts\Verifiable;
use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Verify\Verify;
use LittleApps\LittleJWT\Verify\Verifiers\DefaultVerifier;
use LittleApps\LittleJWT\Verify\Verifiers\StackVerifier;

use Illuminate\Contracts\Foundation\Application;

use Jose\Component\Core\JWK;

class LittleJWT {
    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    /**
     * The JWK to use for building and verifying JWTs
     *
     * @var JWK
     */
    protected $jwk;

    public function __construct(Application $app, JWK $jwk) {
        $this->app = $app;
        $this->jwk = $jwk;
    }

    /**
     * Creates a signed JWT
     *
     * @param \Closure $callback Callback that receives LittleApps\LittleJWT\Builder instance
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return string
     */
    public function createToken(Closure $callback = null, $applyDefault = true) {
        return (string) $this->createJWT($callback, $applyDefault);
    }

    /**
     * Allows for a JWT to be built.
     *
     * @return Build Build instance without any callbacks.
     */
    public function buildJWT() {
        $build = new Build($this->app, $this->jwk);

        return $build;
    }

    /**
     * Creates a signed JWT instance.
     *
     * @param \Closure $callback Callback that receives LittleApps\LittleJWT\Builder instance
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return JWT
     */
    public function createJWT(callable $callback = null, $applyDefault = true) {
        $build = $this->buildJWT();

        if ($applyDefault)
            $build->addCallback($this->getDefaultBuildableCallback());

        if (!is_null($callback))
            $build->addCallback($callback);

        return $build->build();
    }

    /**
     * Builds a JWT instance from a string.
     * This does NOT verify that the JWT is valid.
     *
     * @param string $token
     * @return \LittleApps\LittleJWT\JWT\JWT|null Returns JWT or null if token cannot be parsed.
     */
    public function parseToken(string $token) {
        $builder = $this->app->make(JWTBuilder::class);

        try {
            return $builder->buildFromExisting($token);
        } catch (CantParseJWTException) {
            return null;
        }
    }

    /**
     * Creates a Verify instance for checking if a JWT is valid.
     *
     * @param JWT $jwt JWT instance to verify (generated by parseToken() method)
     * @param callable|Verifiable $callback Callback or Verifiable that recieves Verifier to set checks for JWT.
     * @return Verify Verify instance (before verification is done)
     */
    public function verifyJWT(JWT $jwt, $callback = null, $applyDefault = true) {
        if (is_object($callback) && $callback instanceof Verifiable) {
            $verifiable = $callback;
        } else {
            $callbacks = [];

            if ($applyDefault)
                array_push($callbacks, $this->getDefaultVerifiableCallback());

            if (is_callable($callback))
                array_push($callbacks, $callback);

            $verifiable = new StackVerifier($callbacks);
        }

        $verify = new Verify($this->app, $jwt, $this->jwk, $verifiable);

        return $verify;
    }

    /**
     * Verifies that a JWT is valid.
     *
     * @param JWT $jwt JWT instance to verify (generated by parseToken() method)
     * @param callable $callback Callback that recieves Verifier to set checks for JWT.
     * @return bool True if token is valid.
     */
    public function verifiedJWT(JWT $jwt, callable $callback = null, $applyDefault = true) {
        return $this->verifyJWT($jwt, $callback, $applyDefault)->passes();
    }

    /**
     * Verifies that a token is valid.
     *
     * @param string $token JWT as string to verify.
     * @param callable $callback Callback that recieves Verifier to set checks for JWT.
     * @return bool True if token is valid.
     */
    public function verifiedToken(string $token, callable $callback = null, $applyDefault = true) {
        $jwt = $this->parseToken($token);

        return !is_null($jwt) ? $this->verifiedJWT($jwt, $callback, $applyDefault) : false;
    }

    /**
     * Gets the default buildable callback.
     *
     * @return callable
     */
    protected function getDefaultBuildableCallback() {
        $buildable = $this->app->make(DefaultBuilder::class);

        return [$buildable, 'build'];
    }

    /**
     * Gets the default verifier callback.
     *
     * @return callable
     */
    protected function getDefaultVerifiableCallback() {
        $verifiable = $this->app->make(DefaultVerifier::class);

        return [$verifiable, 'verify'];
    }
}
