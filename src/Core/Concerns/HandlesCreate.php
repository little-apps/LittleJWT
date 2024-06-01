<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;

trait HandlesCreate
{
    use AutoSigns;
    use HandlesCreateSigned;
    use HandlesCreateUnsigned;

    /**
     * Creates an signed or unsigned (depending if auto sign is enabled) JWT instance.
     *
     * @param  callable(Builder): void  $callback  Callback that receives Builder instance.
     * @return JsonWebToken|SignedJsonWebToken
     */
    public function create(?callable $callback = null)
    {
        return $this->autoSign ? $this->createSigned($callback) : $this->createUnsigned($callback);
    }
}
