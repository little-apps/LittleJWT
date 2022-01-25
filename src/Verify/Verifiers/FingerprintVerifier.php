<?php

namespace LittleApps\LittleJWT\Verify\Verifiers;

use LittleApps\LittleJWT\Contracts\Validatable;

use LittleApps\LittleJWT\Verify\Validator;

class FingerprintVerifier implements Validatable
{
    protected $fingerprintHash;

    public function __construct(string $fingerprintHash)
    {
        $this->fingerprintHash = $fingerprintHash;
    }

    public function verify(Validator $verifier)
    {
        $verifier
            ->secureEquals('fgpt', $this->fingerprintHash)
            ->contains(['fgpt']);
    }
}
