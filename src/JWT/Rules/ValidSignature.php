<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use InvalidArgumentException;
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
     * @param  JsonWebKey  $jwk  JWK to verify against.
     */
    public function __construct(JsonWebKey $jwk)
    {
        $this->jwk = $jwk;
    }

    /**
     * {@inheritDoc}
     */
    public function passes(JsonWebToken $jwt)
    {
        try {
            return JWTHasher::verify($this->jwk->algorithm(), $this->jwk, $jwt);
        } catch (InvalidArgumentException $ex) {
            /**
             * The JWT library throws InvalidArgumentException when there's an issue with the algorithm.
             * One example is if the secret phrase is too small or empty.
             */

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function message()
    {
        return 'The signature could not be verified.';
    }
}
