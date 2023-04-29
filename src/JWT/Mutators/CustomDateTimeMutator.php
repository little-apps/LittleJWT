<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\Mutators\Concerns\MutatesDateTime;

class CustomDateTimeMutator implements Mutator
{
    use MutatesDateTime;

    /**
     * Serializes claim value
     *
     * @param mixed $value Claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @return mixed
     */
    public function serialize($value, string $key, array $args)
    {
        [$format] = $args;

        return $this->createCarbonInstance($value)->format($format);
    }

    /**
     * Unserializes claim value
     *
     * @param mixed $value Claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @return mixed
     */
    public function unserialize($value, string $key, array $args)
    {
        [$format] = $args;

        return $this->createCarbonInstance($value, $format);
    }
}
