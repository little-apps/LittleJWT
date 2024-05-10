<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use DateTimeInterface;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class DateTimeMutator implements Mutator
{
    use Concerns\MutatesDateTime;

    public static $format = DateTimeInterface::ISO8601;

    /**
     * {@inheritDoc}
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return $this->createCarbonInstance($value)->format(static::$format);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return $this->createCarbonInstance($value, static::$format);
    }
}
