<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Validation\Valid;

class TestValid extends Valid
{
    /**
     * Initializes TestValid instance.
     *
     * @param Application $app Application container.
     * @param JWT $jwt JWT to test.
     * @param JWK $jwk JWK to verify with.
     */
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
