<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class CustomDateTimeMutator implements Mutator
{
    use Concerns\MutatesDateTime;

    /**
     * @inheritDoc
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        [$format] = $args;

        return $this->createCarbonInstance($value)->format($format);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        [$format] = $args;

        return $this->createCarbonInstance($value, $format);
    }
}
