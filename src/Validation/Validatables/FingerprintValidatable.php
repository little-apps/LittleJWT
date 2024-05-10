<?php

namespace LittleApps\LittleJWT\Validation\Validatables;

use LittleApps\LittleJWT\Validation\Validator;

/**
 * Used by the fingerprint guard adapter to check the JWT has the correct fingerprint hash.
 *
 * @see https://docs.getlittlejwt.com/en/guard#fingerprint-adapter-fingerprint
 */
class FingerprintValidatable
{
    /**
     * Expected fingerprint hash.
     *
     * @var string
     */
    protected $fingerprintHash;

    /**
     * Initalizes fingerprint validatable.
     *
     * @param  string  $fingerprintHash  Expected fingerprint hash.
     */
    public function __construct(string $fingerprintHash)
    {
        $this->fingerprintHash = $fingerprintHash;
    }

    /**
     * Applies validator rules.
     *
     * @return void
     */
    public function __invoke(Validator $validator)
    {
        $validator
            ->secureEquals('fgpt', $this->fingerprintHash)
            ->contains(['fgpt']);
    }
}
