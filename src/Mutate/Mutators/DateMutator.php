<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class DateMutator implements Mutator
{
    use Concerns\MutatesDateTime;

    public static $format = 'Y-m-d';

    /**
     * {@inheritDoc}
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return $this->createCarbonInstance($value)->startOfDay()->format(static::$format);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return $this->createCarbonInstance($value, static::$format)->startOfDay();
    }
}
