<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

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
        $validatable = $this->container->make('littlejwt.validatables.guard');

        return $validatable;
    }
}
