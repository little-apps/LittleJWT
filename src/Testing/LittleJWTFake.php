<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\ForwardsCalls;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\LittleJWT;
use LittleApps\LittleJWT\Validation\Valid;
use LittleApps\LittleJWT\Validation\Validator;
use LittleApps\LittleJWT\Validation\Validators\StackValidator;

class LittleJWTFake
{
    use ForwardsCalls;

    protected $app;
    protected $littleJWT;

    public function __construct(Application $app, LittleJWT $littleJWT)
    {
        $this->app = $app;
        $this->littleJWT = $littleJWT;
    }

    {
    }

    /**
     * Creates a Valid instance for checking if a JWT is valid.
     * The default callback is not added when testing.
     *
     * @param JWT $jwt
     * @param callable $callback Callback that recieves Validator to set checks for JWT.
     * @return Valid Valid instance (before validation is done)
     */
    public function validateJWT(JWT $jwt, callable $callback = null, $applyDefault = false)
    {
        $callbacks = [];

        // Default callback is not added because it expects Validator and not TestValidator
        if (! is_null($callback)) {
            array_push($callbacks, $callback);
        }

        $transformCallbacks = $this->createTransformCallback($callbacks);

        $validator = new StackValidator([$transformCallbacks]);

        return $this->littleJWT->validateJWT($jwt, [$validator, 'validate'], $applyDefault);
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

    /**
     * Forwards method calls to the original LittleJWT instance.
     *
     * @param string $name Method name
     * @param array $parameters Method parameters
     * @return mixed
     */
    public function __call($name, $parameters)
    {
        return $this->forwardCallTo($this->littleJWT, $name, $parameters);
    }
}
