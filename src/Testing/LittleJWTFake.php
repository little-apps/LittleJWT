<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;
use Jose\Component\Core\JWK;


use LittleApps\LittleJWT\LittleJWT;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Validation\Valid;
use LittleApps\LittleJWT\Validation\Validator;
use LittleApps\LittleJWT\Validation\Validators\StackValidator;

class LittleJWTFake extends LittleJWT
{
    protected $app;
    protected $jwk;

    public function __construct(Application $app, JWK $jwk)
    {
        parent::__construct($app, $jwk);
    }

    /**
     * Creates a Valid instance for checking if a JWT is valid.
     * The default callback is not added when testing.
     *
     * @param JWT $jwt
     * @param callable|Validatable $callback Callback or Validatable that recieves Validator to set checks for JWT.
     * @return Valid Valid instance (before validation is done)
     */
    public function validJWT(JWT $jwt, $callback = null, $applyDefault = false)
    {
        $callbacks = [];

        // Default callback is not added because it expects Validator and not TestValidator
        if (! is_null($callback)) {
            array_push($callbacks, $callback);
        }

        $transformCallbacks = $this->createTransformCallback($callbacks);

        $validator = new StackValidator([$transformCallbacks]);

        $valid = new Valid($this->app, $jwt, $this->jwk, $validator);

        return $valid->validate();
    }

    /**
     * Creates the callback which transforms a Validator to TestValidator and sends it through the callbacks.
     *
     * @param iterable $callbacks
     * @return \Closure
     */
    protected function createTransformCallback(iterable $callbacks)
    {
        return function (Validator $validator) use ($callbacks) {
            $testValidator = new TestValidator($this->app, $validator);

            foreach ($callbacks as $callback) {
                $callback($testValidator);
            }
        };
    }
}
