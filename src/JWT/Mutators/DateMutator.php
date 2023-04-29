<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\Mutators\Concerns\MutatesDateTime;

class DateMutator implements Mutator
{
    use MutatesDateTime;

    public static $format = 'Y-m-d';

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
        return $this->createCarbonInstance($value)->startOfDay()->format(static::$format);
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
        return $this->createCarbonInstance($value, static::$format)->startOfDay();
    }
}
