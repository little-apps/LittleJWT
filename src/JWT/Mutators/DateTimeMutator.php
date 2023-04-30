<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use DateTimeInterface;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\Mutators\Concerns\MutatesDateTime;

class DateTimeMutator implements Mutator
{
    use MutatesDateTime;

    public static $format = DateTimeInterface::ISO8601;

    /**
     * Serializes claim value
     *
     * @param mixed $value Unserialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return string|array|int
     */
    public function serialize($value, string $key, array $args, array $claims)
    {
        return $this->createCarbonInstance($value)->format(static::$format);
    }

    /**
     * Unserializes claim value
     *
     * @param string|array|int $value Serialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return mixed
     */
    public function unserialize($value, string $key, array $args, array $claims)
    {
        return $this->createCarbonInstance($value, static::$format);
    }
}
