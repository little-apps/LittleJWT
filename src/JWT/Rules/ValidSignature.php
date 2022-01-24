<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use Illuminate\Support\Facades\App;

use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Factories\JWTHasher;

use LittleApps\LittleJWT\JWT\JWT;

class ValidSignature extends Rule
{
    protected $jwk;

    public function __construct(JWK $jwk)
    {
        $this->jwk = $jwk;
    }

    public function passes(JWT $jwt)
    {
        $hasher = App::make(JWTHasher::class);

        return $hasher->verify($this->jwk, $jwt);
    }

    public function message()
    {
        return 'The signature could not be verified.';
    }
}
