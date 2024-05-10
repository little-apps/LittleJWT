<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class DoubleMutator implements Mutator
{
    /**
     * {@inheritDoc}
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
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
     * {@inheritDoc}
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
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
