<?php

namespace LittleApps\LittleJWT;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Build\Sign;
use LittleApps\LittleJWT\Core\Handler;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\Mutate\Concerns\HasCustomMutators;
use LittleApps\LittleJWT\Mutate\MutateHandler;

/**
 * This class is responsible for generating and validating JSON Web Tokens.
 *
 * @author Nick H <nick@little-apps.com>
 * @license https://github.com/little-apps/LittleJWT/blob/main/LICENSE.md
 *
 * @see https://www.getlittlejwt.com
 *
 * @mixin \LittleApps\LittleJWT\Mutate\MutateHandler
 * @mixin \LittleApps\LittleJWT\Core\Handler
 */
class LittleJWT
{
    use ForwardsCalls;
    use HasCustomMutators;
    use Macroable {
        __call as macroCall;
    }

    /**
     * Application container
     */
    protected readonly Container $app;

    /**
     * The JWK to use for building and validating JWTs
     */
    protected readonly JsonWebKey $jwk;

    /**
     * Whether to use mutations or not
     *
     * @var bool
     */
    protected $mutate;

    /**
     * Holds single Handler instance.
     *
     * @var Handler|null
     */
    protected $handler;

    /**
     * Holds single MutateHandler instance.
     *
     * @var MutateHandler|null
     */
    protected $mutateHandler;

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
        $this->mutate = true;
    }

    /**
     * Gets handler for JWTs
     *
     * @return Handler|MutateHandler
     */
    public function handler()
    {
        return $this->mutate ? $this->withMutate() : $this->withoutMutate();
    }

    /**
     * Handles JWTs with mutations
     *
     * @return MutateHandler
     */
    public function withMutate()
    {
        if (is_null($this->mutateHandler)) {
            $this->mutateHandler = new MutateHandler($this->app, $this->jwk, $this->customMutatorsMapping, true);
        }

        return $this->mutateHandler;
    }

    /**
     * Handles JWTs without mutations
     *
     * @return Handler
     */
    public function withoutMutate()
    {
        if (is_null($this->handler)) {
            $this->handler = new Handler($this->app, $this->jwk);
        }

        return $this->handler;
    }

    /**
     * Whether to always use mutations.
     * If enabled, all operations will be handled with the MutateHandler.
     *
     * @return $this
     */
    public function alwaysMutate(bool $enabled)
    {
        $this->mutate = $enabled;

        return $this;
    }

    /**
     * Gets the JWK used for signing and validating.
     */
    public function getJwk(): JsonWebKey
    {
        return $this->jwk;
    }

    /**
     * Forwards method calls to handler.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($this->hasMacro($name)) {
            return $this->macroCall($name, $arguments);
        }

        return $this->forwardCallTo($this->handler(), $name, $arguments);
    }
}
