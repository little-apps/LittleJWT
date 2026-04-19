<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;

trait HandlesCreateSigned
{
    /**
     * Creates a signed JWT instance.
     *
     * @param  callable(Builder): void  $callback  Callback that receives Builder instance.
     * @return SignedJsonWebToken
     */
    public function createSigned(?callable $callback = null)
    {
        $unsigned = $this->createUnsigned($callback);

        return JWTHasher::sign($unsigned, $this->jwk);
    }
}
