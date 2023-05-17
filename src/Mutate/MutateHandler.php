<?php

namespace LittleApps\LittleJWT\Mutate;

use Illuminate\Contracts\Foundation\Application;

use LittleApps\LittleJWT\Concerns\PassableThru;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Core\Handler;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\MutatedJsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;
use LittleApps\LittleJWT\Validation\Validator;
use LittleApps\LittleJWT\LittleJWT;

class MutateHandler extends Handler
{
    use PassableThru {
        runThru as passableRunThru;
    }
    use Concerns\HasCustomMutators;
    use Concerns\HasDefaultMutators;

    /**
     * Intializes LittleJWT instance.
     *
     * @param Application $app Application container
     * @param JsonWebKey $jwk JWK to sign and verify JWTs with.
     * @param array<string, \LittleApps\LittleJWT\Contracts\Mutator> $customMutators Custom mutators.
     * @param bool $applyDefault Whether to apply default mutators.
     */
    public function __construct(Application $app, JsonWebKey $jwk, array $customMutators, bool $applyDefault)
    {
        parent::__construct($app, $jwk);

        $this->defaultMutators = $applyDefault;
        $this->customMutatorsMapping = $customMutators;
    }

    /**
     * Adds callback that specifies Mutators when called.
     *
     * @param callable(Mutators): void $callback
     * @return self New instance with callback (and any callbacks from this handler)
     */
    public function mutate(callable $callback)
    {
        $instance = new self($this->app, $this->jwk, $this->customMutatorsMapping, $this->defaultMutators);

        // Add mutators from this instance
        foreach ($this->passThruStack as $cb) {
            $instance->passMutatorsThru($cb);
        }

        $instance->passMutatorsThru($callback);

        return $instance;
    }

    /**
     * Passes a Mutators instance through a callback.
     *
     * @param callable(Mutators): void $callback
     * @return $this
     */
    public function passMutatorsThru(callable $callback)
    {
        return $this->passThru($callback);
    }

    /**
     * Serializes JWT
     *
     * @param JsonWebToken $jwt JWT to serialize.
     * @param Mutators|null $mutators An existing Mutators instance (optional)
     * @return JsonWebToken
     */
    public function serialize(JsonWebToken $jwt, Mutators $mutators = null)
    {
        $mutators = $mutators ?? new Mutators();

        $this->runThru($mutators);

        return $this->createMutate()->serialize($mutators, $jwt);
    }

    /**
     * Unserializes JWT
     *
     * @param JsonWebToken $jwt JWT to unserialize.
     * @param Mutators|null $mutators An existing Mutators instance (optional)
     * @return MutatedJsonWebToken
     */
    public function unserialize(JsonWebToken $jwt, Mutators $mutators = null)
    {
        $mutators = $mutators ?? new Mutators();

        $this->runThru($mutators);

        return $this->createMutate()->unserialize($mutators, $jwt);
    }

    /**
     * Creates an unsigned serialized JWT instance.
     *
     * @param callable(Builder): void $callback Callback that receives Builder instance.
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return SignedJsonWebToken|JsonWebToken
     */
    public function create(callable $callback = null, $applyDefault = true)
    {
        // Don't sign it yet, because this will try to JSON encode the claims (which may need to be serialized before that can happen)
        $unsigned = parent::createUnsigned($callback, $applyDefault);

        $serialized = $this->serialize($unsigned);

        return $this->autoSign ? $serialized->sign() : $serialized;
    }

    /**
     * Validates a JSON Web Token (JWT).
     *
     * @param JsonWebToken $jwt JWT instance to validate (generated by parseToken() method)
     * @param callable(Validator): void $callback Callable that receives Validator to set rules for JWT.
     * @param bool $applyDefault If true, the default validatable is used first. (default: true)
     * @return MutatedValidatedJsonWebToken
     */
    public function validate(JsonWebToken $jwt, callable $callback = null, $applyDefault = true)
    {
        $result = parent::validate($jwt, $callback, $applyDefault);

        return new MutatedValidatedJsonWebToken($result, fn () => $this->unserialize($result->getJWT()));
    }

    /**
     * Sends parameters through callbacks.
     *
     * @param array ...$params
     * @return $this
     */
    protected function runThru(...$params)
    {
        if ($this->defaultMutators) {
            call_user_func_array($this->createCallbackBuilder()->createMutatableCallback(), $params);
        }

        return $this->passableRunThru(...$params);
    }

    /**
     * Creates Mutate instance.
     *
     * @return Mutate
     */
    protected function createMutate()
    {
        $manager = new MutatorManager($this->app, $this->getCustomMutators());

        return new Mutate($this->createJWTBuilder(), $manager);
    }
}
