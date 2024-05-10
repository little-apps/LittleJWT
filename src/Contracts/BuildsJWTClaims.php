<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\JWT\ClaimManagers;

interface BuildsJWTClaims
{
    /**
     * Gets the JWT claims.
     */
    public function getClaimManagers(): ClaimManagers;
}
