<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Contracts\Foundation\Application;
use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Contracts\Buildable;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\JWT\ClaimManager;

class Build
{
    protected $app;

    protected $jwk;

    protected $buildable;

    public function __construct(Application $app, JWK $jwk, Buildable $buildable)
    {
        $this->app = $app;
        $this->jwk = $jwk;
        $this->buildable = $buildable;
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
     * Creates the Builder instance
     *
     * @return Builder
     */
    protected function createBuilder()
    {
        return tap(new Builder(), function (Builder $builder) {
            $this->buildable->build($builder);
        });
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
