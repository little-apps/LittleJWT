<?php

namespace LittleApps\LittleJWT\Facades;

use Illuminate\Support\Facades\Facade;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Contracts\Keyable;
use LittleApps\LittleJWT\LittleJWT as LittleJWTInstance;
use LittleApps\LittleJWT\Testing\LittleJWT as LittleJWTFake;

class LittleJWT extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return \Illuminate\Support\Testing\Fakes\EventFake
     */
    public static function fake(JWK $jwk = null)
    {
        if (is_null($jwk)) {
            // Use random JWK if null is specified.
            $jwk = static::$app->make(Keyable::class)->generateRandomJwk();
        }

        static::swap($fake = new LittleJWTFake(static::$app, new LittleJWTInstance(static::$app, $jwk)));

        return $fake;
    }

    /**
     * Creates a new instance of LittleJWT to use to build/validate with a different JWK.
     *
     * @param JWK $jwk
     * @param Closure|null $callback If not null, called with new LittleJWT instance as parameter.
     * @return LittleJWTInstance New LittleJWT instance
     */
    public static function withJwk(JWK $jwk)
    {
        $jwtInstance = new LittleJWTInstance(static::$app, $jwk);

        return (static::getFacadeRoot() instanceof LittleJWTFake) ? new LittleJWTFake(static::$app, $jwtInstance) : $jwtInstance;
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
