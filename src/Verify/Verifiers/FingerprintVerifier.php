<?php

namespace LittleApps\LittleJWT\Verify\Verifiers;

use LittleApps\LittleJWT\Contracts\Verifiable;
use LittleApps\LittleJWT\Verify\Verifier;

class FingerprintVerifier implements Verifiable
{
    protected $fingerprintHash;

    public function __construct(string $fingerprintHash)
    {
        $this->fingerprintHash = $fingerprintHash;
    }

    public function verify(Verifier $verifier)
    {
        $verifier
            ->secureEquals('fgpt', $this->fingerprintHash)
            ->contains(['fgpt']);
    }
}
