<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Build\Build;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;

trait HandlesCreateUnsigned
{
    /**
     * Creates an unsigned JWT instance.
     *
     * @param  callable(Builder): void  $callback  Callback that receives Builder instance.
     * @return JsonWebToken
     */
    public function createUnsigned(?callable $callback = null)
    {
        return
            $this->build()
                ->passBuilderThru($callback ?? fn () => null)
                ->build();
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

    /**
     * Creates JWT Builder
     *
     * @return JWTBuilder
     */
    public function createJWTBuilder()
    {
        return new JWTBuilder();
    }
}
