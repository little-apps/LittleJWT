<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use LittleApps\LittleJWT\Validation\Validators;

class GenericAdapter extends AbstractAdapter
{
    use Concerns\BuildsJwt;

    /**
     * Gets a callback that receives a Validator to specify the JWT validations.
     *
     * @return callable
     */
    protected function getValidatorCallback()
    {
        $validatable = new Validators\GuardValidator($this->container, $this->config['model']);

        return [$validatable, 'validate'];
    }
}
