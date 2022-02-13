<?php

namespace LittleApps\LittleJWT\Validation\Validatables;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

class FingerprintValidatable implements Validatable
{
    protected $fingerprintHash;

    public function __construct(string $fingerprintHash)
    {
        $this->fingerprintHash = $fingerprintHash;
    }

    public function validate(Validator $validator)
    {
        $validator
            ->secureEquals('fgpt', $this->fingerprintHash)
            ->contains(['fgpt']);
    }
}
