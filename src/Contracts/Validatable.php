<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\Validation\Validator;

/**
 * Validatable interface
 *
 * @deprecated 1.6.0 Deprecated in favor of invokable classes
 */
interface Validatable
{
    /**
     * Performs the validation on a JWT.
     *
     * @return void
     */
    public function validate(Validator $validator);
}
