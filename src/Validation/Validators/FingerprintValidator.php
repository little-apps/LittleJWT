<?php

namespace LittleApps\LittleJWT\Validation\Validators;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

class FingerprintValidator implements Validatable
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
