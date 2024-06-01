<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Factories\JWTHasher;

trait HandlesCreateSigned
{
    /**
     * Creates a signed JWT instance.
     *
     * @param  callable(\LittleApps\LittleJWT\Build\Builder): void  $callback  Callback that receives Builder instance.
     * @return \LittleApps\LittleJWT\JWT\SignedJsonWebToken
     */
    public function createSigned(?callable $callback = null)
    {
        $unsigned = $this->createUnsigned($callback);

        return JWTHasher::sign($unsigned, $this->jwk);
    }
}
