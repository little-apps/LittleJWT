<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\ForwardsCalls;

use LittleApps\LittleJWT\Concerns\PassableThru;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Mutate\Mutate;
use LittleApps\LittleJWT\Mutate\Mutators;

class Build
{
    use ForwardsCalls;
    use PassableThru;

    /**
     * Application container.
     *
     * @var Application
     */
    protected $app;

    /**
     * Builder to build JWTs.
     *
     * @var Builder
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
     * @param Builder $builder Builder to use (optional).
     */
    public function __construct(Application $app, Builder $builder = null)
    {
        $this->app = $app;

        $this->builder = $builder ?? $this->buildBuilder();
    }

    /**
     * Passes a Builder instance through a callback.
     *
     * @param callable(Builder $builder) $callback

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
    public function build(Mutate $mutate = null)
    {
        $this->mutators = new Mutators();

        $this->runThru($this->builder, $this->mutators);

        $jwt = $this->createJWTBuilder()->buildFromParts($this->builder->getHeaders(), $this->builder->getPayload());

        if (is_null($mutate)) {
            return $jwt;
        }

        // TODO: Move mutation to outside this class:
        //  * Builder and Mutators instances will need to be shared.

        $mutate
            ->passMutatorsThru(function (Mutators $mutators) {
                foreach ($this->builder->getHeadersOptions() as $claimBuildOptions) {
                    if ($claimBuildOptions->hasMutatable()) {
                        $mutators->addHeader($claimBuildOptions->getKey(), $claimBuildOptions->getMutatable());
                    }
                }

                foreach ($this->builder->getPayloadOptions() as $claimBuildOptions) {
                    if ($claimBuildOptions->hasMutatable()) {
                        $mutators->addPayload($claimBuildOptions->getKey(), $claimBuildOptions->getMutatable());
                    }
                }
            })->passMutatorsThru(function (Mutators $mutators) {
                $mutators->merge($this->mutators);
            });

        return $mutate->serialize($jwt);
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
        return new JWTBuilder();
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
