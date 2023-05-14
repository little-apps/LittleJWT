<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Build\Build;
use LittleApps\LittleJWT\Build\Buildables\StackBuildable;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;

trait HandlesCreate
{
    use AutoSigns;

    /**
     * Creates an unsigned JWT instance.
     *
     * @param callable(Builder): void $callback Callback that receives Builder instance.
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return JsonWebToken
     */
    public function createUnsigned(callable $callback = null, $applyDefault = true)
    {
        $callbacks = [];

        if ($applyDefault) {
            array_push($callbacks, $this->createCallbackBuilder()->createBuildableCallback());
        }

        if (is_callable($callback)) {
            array_push($callbacks, $callback);
        }

        $buildable = new StackBuildable($callbacks);

        return
            $this->build()
                ->passBuilderThru($buildable)
                ->build();
    }

    /**
     * Creates a signed JWT instance.
     *
     * @param callable(Builder): void $callback Callback that receives Builder instance.
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return SignedJsonWebToken
     */
    public function createSigned(callable $callback = null, $applyDefault = true)
    {
        $unsigned = $this->createUnsigned($callback, $applyDefault);

        return $unsigned->sign();
    }

    /**
     * Creates an signed or unsigned (depending if auto sign is enabled) JWT instance.
     *
     * @param callable(Builder): void $callback Callback that receives Builder instance.
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return JsonWebToken|SignedJsonWebToken
     */
    public function create(callable $callback = null, $applyDefault = true)
    {
        return $this->autoSign ? $this->createSigned($callback, $applyDefault) : $this->createUnsigned($callback, $applyDefault);
    }

    /**
     * Creates a Build instance.
     *
     * @return Build
     */
    public function build()
    {
        $build = new Build($this->app, $this->createJWTBuilder());

        return $build;
    }
}
