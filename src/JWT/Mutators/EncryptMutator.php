<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use Illuminate\Support\Facades\Crypt;

use LittleApps\LittleJWT\Contracts\Mutator;

class EncryptMutator implements Mutator
{
    /**
     * Serializes claim value
     *
     * @param  mixed  $value Claim value
     * @param  string $key   Claim key
     * @param  array  $args  Any arguments to use for mutation
     * @return mixed
     */
    public function serialize($value, string $key, array $args)
    {
        return Crypt::encrypt($value);
    }

    /**
     * Unserializes claim value
     *
     * @param  mixed  $value Claim value
     * @param  string $key   Claim key
     * @param  array  $args  Any arguments to use for mutation
     * @return mixed
     */
    public function unserialize($value, string $key, array $args)
    {
        return Crypt::decrypt($value);
    }
}
