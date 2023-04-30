<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\ForwardsCalls;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Validation\Valid;
use LittleApps\LittleJWT\JWT\JWT;

class TestValid extends Valid
{
    public function __construct(Application $app, JWT $jwt, JWK $jwk)
    {
        parent::__construct($app, $jwt, $jwk);
    }

    /**
     * Builds a Validator
     *
     * @return Validator
     */
    protected function buildValidator()
    {
        $blacklistManager = $this->app->make(BlacklistManager::class);

        return new TestValidator($this->app, $blacklistManager, $this->jwk);
    }
}
