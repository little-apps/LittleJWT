<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use LittleApps\LittleJWT\Validation\Validators;

class GenericAdapter extends AbstractAdapter
{
    use Concerns\BuildsJwt;

    /**
     * Builds the Validatable used to validate a JWT.
     *
     * @return \LittleApps\LittleJWT\Contracts\Validatable
     */
    protected function buildValidatable()
    {
        return new Validators\GuardValidator($this->container, $this->config['model']);
    }
}
