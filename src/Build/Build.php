<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Contracts\Foundation\Application;
use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Factories\JWTBuilder;

use LittleApps\LittleJWT\Factories\JWTHasher;

use LittleApps\LittleJWT\JWT\ClaimManager;

class Build
{
    protected $app;

    protected $jwk;

    protected $callbacks;

    public function __construct(Application $app, JWK $jwk)
    {
        $this->app = $app;
        $this->jwk = $jwk;
        $this->callbacks = [];
    }

    /**
     * Builds a JWT
     *
     * @return JWT
     */
    public function build()
    {
        $builder = $this->createBuilder();

        $headers = new ClaimManager($builder->getHeaders()->sortKeys()->all());
        $payload = new ClaimManager($builder->getPayload()->sortKeys()->all());

        $signature = $this->createJWTHasher()->hash($this->jwk, $headers, $payload);

        return $this->createJWTBuilder()->buildFromParts($headers, $payload, $signature);
    }

    /**
     * Adds a callback to send Builder through.
     *
     * @param callable $callback
     * @return $this
     */
    public function addCallback(callable $callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Creates the Builder instance
     *
     * @return Builder
     */
    protected function createBuilder()
    {
        $builder = new Builder();

        foreach ($this->callbacks as $callback) {
            $callback($builder);
        }

        return $builder;
    }

    /**
     * Creates the JWTBuilder instance
     *
     * @return JWTBuilder
     */
    protected function createJWTBuilder()
    {
        return $this->app->make(JWTBuilder::class);
    }

    /**
     * Creates the JWTHasher
     *
     * @return JWTHasher
     */
    protected function createJWTHasher()
    {
        return $this->app->make(JWTHasher::class);
    }
}
