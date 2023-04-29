<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;

class DecimalMutator implements Mutator
{
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
        [$decimals] = $args;

        return number_format($value, $decimals ?? 0, '.', '');
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
        return (float) $value;
    }
}
