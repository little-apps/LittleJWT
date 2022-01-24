<?php

namespace LittleApps\LittleJWT\Contracts;

interface Keyable
{
    /**
     * Creates a key to sign and verify JWTs.
     *
     * @return \Jose\Component\Core\JWK
     */
    public function build();
}
