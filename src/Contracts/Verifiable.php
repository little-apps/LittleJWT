<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\Verify\Verifier;

interface Verifiable {
    /**
     * Performs the default verification on a JWT (used by the guard).
     *
     * @param Verifier $verifier
     * @return void
     */
    public function verify(Verifier $verifier);
}
