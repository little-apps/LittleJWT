<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use Illuminate\Support\Facades\App;

use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Factories\JWTHasher;

use LittleApps\LittleJWT\JWT\JWT;

class ValidSignature extends Rule
{
    /**
     * JWK to verify against.
     *
     * @var JWK
     */
    protected $jwk;

    /**
     * Initializes valid signature rule.
     *
     * @param JWK $jwk JWK to verify against.
     */
    public function __construct(JWK $jwk)
    {
        $this->jwk = $jwk;
    }

    /**
     * @inheritDoc
     */
    public function passes(JWT $jwt)
    {
        $hasher = App::make(JWTHasher::class);

        return $hasher->verify($this->jwk, $jwt);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'The signature could not be verified.';
    }
}
