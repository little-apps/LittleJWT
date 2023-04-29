<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use DateTimeInterface;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\Mutators\Concerns\MutatesDateTime;

class DateTimeMutator implements Mutator {
    use MutatesDateTime;

    public static $format = DateTimeInterface::ISO8601;

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
        return $this->createCarbonInstance($value)->format(static::$format);
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
        return $this->createCarbonInstance($value, static::$format);
    }
}
