<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class TimestampMutator implements Mutator
{
    use Concerns\MutatesDateTime;

    public static $format = 'U';

    /**
     * {@inheritDoc}
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        // Uses unix() method instead because format('U') returns number as string.
        return $this->createCarbonInstance($value)->unix();
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return $this->createCarbonInstance($value, static::$format);
    }
}
