<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Build\Build;
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
     * @return JsonWebToken
     */
    public function createUnsigned(callable $callback = null)
    {
        return
            $this->build()
                ->passBuilderThru($callback ?? fn () => null)
                ->build();
    }

    /**
     * Creates a signed JWT instance.
     *
     * @param callable(Builder): void $callback Callback that receives Builder instance.
     * @return SignedJsonWebToken
     */
    public function createSigned(callable $callback = null)
    {
        $unsigned = $this->createUnsigned($callback);

        return $unsigned->sign();
    }

    /**
     * Creates an signed or unsigned (depending if auto sign is enabled) JWT instance.
     *
     * @param callable(Builder): void $callback Callback that receives Builder instance.
     * @return JsonWebToken|SignedJsonWebToken
     */
    public function create(callable $callback = null)
    {
        return $this->autoSign ? $this->createSigned($callback) : $this->createUnsigned($callback);
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
