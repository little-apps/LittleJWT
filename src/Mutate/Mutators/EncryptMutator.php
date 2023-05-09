<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use Illuminate\Support\Facades\Crypt;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class EncryptMutator implements Mutator
{
    /**
     * @inheritDoc
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return Crypt::encrypt($value);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return Crypt::decrypt($value);
    }
}
