<?php

namespace LittleApps\LittleJWT;

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
 * @author Nick H <nick@little-apps.com>
 * @license https://github.com/little-apps/LittleJWT/blob/main/LICENSE.md
 * @see https://www.getlittlejwt.com
 * @mixin \LittleApps\LittleJWT\Mutate\MutateHandler
 * @mixin \LittleApps\LittleJWT\Core\Handler
 */
class LittleJWT
{
    use Macroable {
        __call as macroCall;
    }
    use ForwardsCalls;
    use HasCustomMutators;


    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    /**
     * The JWK to use for building and validating JWTs
     *
     * @var JsonWebKey
     */
    protected $jwk;

    /**
     * Whether to use mutations or not
     *
     * @var bool
     */
    protected $mutate;

    /**
     * Intializes LittleJWT instance.
     *
     * @param Application $app Application container
     * @param JsonWebKey $jwk JWK to sign and verify JWTs with.
     */
    public function __construct(Application $app, JsonWebKey $jwk)
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
        return new MutateHandler($this->app, $this->jwk, $this->customMutatorsMapping, true);
    }

    /**
     * Handles JWTs without mutations
     *
     * @return Handler
     */
    public function withoutMutate()
    {
        return new Handler($this->app, $this->jwk);
    }

    /**
     * Whether to always use mutations.
     * If enabled, all operations will be handled with the MutateHandler.
     *
     * @param bool $enabled
     * @return $this
     */
    public function alwaysMutate(bool $enabled)
    {
        $this->mutate = $enabled;

        return $this;
    }

    /**
     * Forwards method calls to handler.
     *
     * @param string $name
     * @param array $arguments
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
