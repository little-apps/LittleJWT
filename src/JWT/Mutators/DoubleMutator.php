<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;

class DoubleMutator implements Mutator
{
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
        if (is_infinite($value)) {
            $value = $value !== -INF ? 'Infinity' : '-Infinity';
        } elseif (is_nan($value)) {
            $value = 'NaN';
        } else {
            $value = (string) $value;
        }

        return $value;
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
        if ($value === 'Infinity') {
            $value = INF;
        } elseif ($value === '-Infinity') {
            $value = -INF;
        } elseif ($value === 'NaN') {
            $value = NAN;
        } else {
            $value = (float) $value;
        }

        return $value;
    }
}
