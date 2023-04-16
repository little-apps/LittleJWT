<?php

namespace LittleApps\LittleJWT\Validation\Validatables;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

/**
 * Used by the fingerprint guard adapter to check the JWT has the correct fingerprint hash.
 * @see https://docs.getlittlejwt.com/en/guard#fingerprint-adapter-fingerprint
 */
class FingerprintValidatable
{
    protected $fingerprintHash;

    public function __construct(string $fingerprintHash)
    {
        $this->fingerprintHash = $fingerprintHash;
    }

    public function __invoke(Validator $validator)
    {
        $validator
            ->secureEquals('fgpt', $this->fingerprintHash)
            ->contains(['fgpt']);
    }
}
