<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Contracts\Foundation\Application;

use LittleApps\LittleJWT\Concerns\PassableThru;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Mutate\Mutators;

class Build
{
    use PassableThru;

    /**
     * Application container.
     *
     * @var Application
     */
    protected $app;

    /**
     * Builder to build JWTs
     *
     * @var JWTBuilder
     */
    protected $builder;

    /**
     * Mutators to use for serializing.
     * Populated when build() is called.
     *
     * @var Mutators
     */
    protected $mutators;

    /**
     * Initializes Build instance.
     *
     * @param Application $app Application container.
     * @param JWTBuilder $jwtBuilder JWTBuilder for creating JWTs.
     */
    public function __construct(Application $app, JWTBuilder $jwtBuilder)
    {
        $this->app = $app;

        $this->builder = $jwtBuilder;
    }

    /**
     * Passes a Builder instance through a callback.
     *
     * @param callable(Builder): void $callback

     * @return $this
     */
    public function passBuilderThru(callable $callback)
    {
        return $this->passThru($callback);
    }

    /**
     * Builds a JWT
     *
     * @return \LittleApps\LittleJWT\JWT\JsonWebToken
     */
    public function build()
    {
        $builder = $this->createBuilder();

        $this->runThru($builder);

        return $this->builder->buildFromParts($builder->getHeaders(), $builder->getPayload());
    }

    /**
     * Builds the Builder.
     *
     * @return Builder
     */
    protected function createBuilder()
    {
        $headerClaims = $this->app->config->get('littlejwt.builder.claims.header', []);
        $payloadClaims = $this->app->config->get('littlejwt.builder.claims.payload', []);

        return new Builder($headerClaims, $payloadClaims);
    }
}
