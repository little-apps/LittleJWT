<?php

namespace LittleApps\LittleJWT\Facades;

use Illuminate\Support\Facades\Facade;

use LittleApps\LittleJWT\LittleJWT as LittleJWTInstance;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Contracts\KeyBuildable;

use LittleApps\LittleJWT\Testing\LittleJWTFake;

class LittleJWT extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return \Illuminate\Support\Testing\Fakes\EventFake
     */
    public static function fake(JWK $jwk = null)
    {
        $jwk = $jwk ?? static::$app->make(KeyBuildable::class)->build();

        static::swap($fake = new LittleJWTFake(static::$app, $jwk));

        return $fake;
    }

    /**
     * Creates a new instance of LittleJWT to use to build/verify with a different JWK.
     *
     * @param JWK $jwk
     * @param Closure|null $callback If not null, called with new LittleJWT instance as parameter.
     * @return LittleJWTInstance New LittleJWT instance
     */
    public static function withJwk(JWK $jwk) {
        return static::getMockableClass() !== LittleJWTFake::class ? new LittleJWTInstance(static::$app, $jwk) : new LittleJWTFake(static::$app, $jwk);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'littlejwt';
    }
}
