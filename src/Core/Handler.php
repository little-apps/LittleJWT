<?php

namespace LittleApps\LittleJWT\Core;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\JWK\JsonWebKey;

class Handler
{
    use Concerns\CreatesCallbackBuilder;
    use Concerns\HandlesCreate;
    use Concerns\HandlesParse;
    use Concerns\HandlesValidate;

    /**
     * Application container
     */
    protected readonly Container $app;

    /**
     * The JWK to use for building and validating JWTs
     */
    protected readonly JsonWebKey $jwk;

    /**
     * Intializes LittleJWT instance.
     *
     * @param  Container  $app  Application container
     * @param  JsonWebKey  $jwk  JWK to sign and verify JWTs with.
     */
    public function __construct(Container $app, JsonWebKey $jwk)
    {
        $this->app = $app;
        $this->jwk = $jwk;
    }
}
