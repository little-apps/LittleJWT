<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Build\Sign;

trait HandlesSigning
{
    /**
     * Creates a Sign instance.
     *
     * @return Sign
     */
    public function sign()
    {
        return new Sign($this->jwk);
    }
}
