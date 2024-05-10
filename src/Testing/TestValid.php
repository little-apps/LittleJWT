<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Contracts\BuildsValidatorRules;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\Validation\Valid;
use LittleApps\LittleJWT\Validation\Validator;
use LittleApps\LittleJWT\Validation\Validators;

class TestValid extends Valid
{
    /**
     * Initializes TestValid instance.
     *
     * @param Application $app Application container.
     * @param JsonWebToken $jwt JWT to test.
     * @param JWK $jwk JWK to verify with.
     */
    public function __construct(Application $app, JsonWebToken $jwt, JWK $jwk)
    {
        parent::__construct($app, $jwt, $jwk);
    }

    /**
     * Builds a Validator
     *
     * @return BuildsValidatorRules
     */
    protected function buildValidator(): BuildsValidatorRules
    {
        $blacklistManager = $this->app->make(BlacklistManager::class);

        return new TestValidator($this->app, $blacklistManager, $this->jwk);
    }
}
