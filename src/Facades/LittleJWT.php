<?php

namespace LittleApps\LittleJWT\Facades;

use Illuminate\Support\Facades\Facade;
use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Contracts\Keyable;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\LittleJWT as LittleJWTInstance;
use LittleApps\LittleJWT\Testing\LittleJWT as LittleJWTFake;

/**
 * @mixin \LittleApps\LittleJWT\LittleJWT
 */
class LittleJWT extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return LittleJWTFake
     */
    public static function fake(?JsonWebKey $jwk = null)
    {
        if (is_null($jwk)) {
            // Use random JWK if null is specified.
            $jwk = KeyBuilder::buildFromConfig([
                'default' => KeyBuilder::KEY_RANDOM
            ]);
        }

        static::swap($fake = new LittleJWTFake(static::$app, $jwk));

        return $fake;
    }

    /**
     * Creates a new instance of LittleJWT to use to build/validate with a different JWK.
     *
     * @return LittleJWTInstance New LittleJWT instance
     */
    public static function withJwk(JsonWebKey $jwk)
    {
        $jwtInstance = new LittleJWTInstance(static::$app, $jwk);

        return (static::getFacadeRoot() instanceof LittleJWTFake) ? new LittleJWTFake(static::$app, $jwk) : $jwtInstance;
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
