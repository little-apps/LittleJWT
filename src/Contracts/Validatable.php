<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\Validation\Validator;

interface Validatable
{
    /**
     * Performs the validation on a JWT.
     *
     * @param Validator $validator
     * @return void
     */
    public function validate(Validator $validator);
}
