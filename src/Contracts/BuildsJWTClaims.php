<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\JWT\ClaimManagers;

interface BuildsJWTClaims
{
    /**
     * Gets the JWT claims.
     *
     * @return ClaimManagers
     */
    public function getClaimManagers(): ClaimManagers;
}
