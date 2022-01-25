<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\Verify\Validator;

interface Validatable
{
    /**
     * Performs the default verification on a JWT (used by the guard).
     *
     * @param Validator $verifier
     * @return void
     */
    public function verify(Validator $verifier);
}
