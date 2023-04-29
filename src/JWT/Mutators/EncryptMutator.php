<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;

use Illuminate\Support\Facades\Crypt;

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
