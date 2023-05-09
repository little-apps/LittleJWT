<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class ValidSignature extends Rule
{
    /**
     * JsonWebKey to use for verification.
     *
     * @var JsonWebKey
     */
    protected $jwk;

    /**
     * Initializes valid signature rule.
     *
     * @param JsonWebKey $jwk JWK to verify against.
     */
    public function __construct(JsonWebKey $jwk)
    {
        $this->jwk = $jwk;
    }

    /**
     * @inheritDoc
     */
    public function passes(JsonWebToken $jwt)
    {
        return JWTHasher::verify($this->jwk->algorithm(), $this->jwk, $jwt);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'The signature could not be verified.';
    }
}
