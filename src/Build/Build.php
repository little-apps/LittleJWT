<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\ForwardsCalls;
use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Contracts\Buildable;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\JWT\ClaimManager;

class Build
{
    use ForwardsCalls;

    protected $app;

    protected $jwk;

    protected $builder;

    public function __construct(Application $app, JWK $jwk)
    {
        $this->app = $app;
        $this->jwk = $jwk;
        $this->builder = $this->buildBuilder();
    }

    /**
     * Passes a Builder instance through a Buildable instance
     *
     * @param Buildable $buildable
     * @return $this
     */
    public function passBuilderThru(Buildable $buildable)
    {
        $buildable->build($this->builder);

        return $this;
    }

    /**
     * Builds a JWT
     *
     * @return JWT
     */
    public function build()
    {
        $headers = new ClaimManager($this->builder->getHeaders()->sortKeys()->all());
        $payload = new ClaimManager($this->builder->getPayload()->sortKeys()->all());

        $signature = $this->createJWTHasher()->hash($this->jwk, $headers, $payload);

        return $this->createJWTBuilder()->buildFromParts($headers, $payload, $signature);
    }

    /**
     * Builds the Builder.
     *
     * @return Builder
     */
    protected function buildBuilder()
    {
        $headerClaims = $this->app->config->get('littlejwt.builder.claims.header', []);
        $payloadClaims = $this->app->config->get('littlejwt.builder.claims.payload', []);

        return new Builder($headerClaims, $payloadClaims);
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

    /**
     * Forwards method calls to the Builder instance.
     *
     * @param string $name Method name
     * @param array $parameters Method parameters
     * @return mixed Returns $this if Builder instance is returned from forwarded method call.
     */
    public function __call($name, $parameters)
    {
        // Use forwardCallTo since Laravel 7.x doesn't support forwardDecoratedCallTo
        $ret = $this->forwardCallTo($this->builder, $name, $parameters);

        return $ret === $this->builder ? $this : $ret;
    }
}
