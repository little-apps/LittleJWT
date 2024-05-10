<?php

namespace LittleApps\LittleJWT\Mutate;

use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\Exceptions\CantResolveMutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use Throwable;

/**
 * Allows for claims to be serialized and deserialized.
 * The serialization happens before the JWT is JSON encoded and the unserialization happens after the JWT is decoded.
 */
class MutatorManager
{
    /**
     * Mutator Resolver
     *
     * @var MutatorResolver
     */
    protected $resolver;

    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    public function __construct(MutatorResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Serializes claim value for JWT.
     *
     * @param  string  $key
     * @param  mixed  $definition  Mutator definition
     * @param  mixed  $value  The claim value.
     * @param  JsonWebToken  $jwt  Original JWT.
     * @return mixed
     */
    public function serialize($key, $definition, $value, JsonWebToken $jwt)
    {
        return $this->serializeAs($key, $value, $definition, $jwt);
    }

    /**
     * Unserializes claim value back to original.
     * The claim value will still be sent through json_decode after.
     *
     * @param  string  $key
     * @param  mixed  $definition  Mutator definition
     * @param  mixed  $value  The claim value.
     * @param  JsonWebToken  $jwt  Original JWT.
     * @return mixed
     */
    public function unserialize($key, $definition, $value, JsonWebToken $jwt)
    {
        try {
            $value = $this->unserializeAs($key, $value, $definition, $jwt);
        } catch (Throwable $ex) {
            throw new CantParseJWTException($ex);
        }

        return $value;
    }

    /**
     * Serializes claim for storing in JWT.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string|Mutator  $definition
     * @param  JsonWebToken  $jwt  Original JWT.
     * @return string
     */
    protected function serializeAs($key, $value, $definition, JsonWebToken $jwt)
    {
        try {
            [$mutator, $args] = $this->resolver->resolve($definition);

            return $this->serializeThruMutator($mutator, $value, $key, $args, $jwt);
        } catch (CantResolveMutator $ex) {
        }

        return $value;
    }

    /**
     * Performs serialization through Mutator instance.
     *
     * @param  mixed  $value
     * @param  string  $key
     * @return mixed
     */
    protected function serializeThruMutator(Mutator $mutator, $value, $key, array $args, JsonWebToken $jwt)
    {
        return $mutator->serialize($value, $key, $args, $jwt);
    }

    /**
     * Unserializes a claim to a type definition
     *
     * @param  string  $key  Claim key
     * @param  mixed  $value  Claim value
     * @param  string|Mutator  $definition  Type definition
     * @param  JsonWebToken  $jwt  Original JWT
     * @return mixed
     */
    protected function unserializeAs($key, $value, $definition, JsonWebToken $jwt)
    {
        try {
            [$mutator, $args] = $this->resolver->resolve($definition);

            return $this->unserializeThruMutator($mutator, $value, $key, $args, $jwt);
        } catch (CantResolveMutator $ex) {
        }

        return $value;
    }

    /**
     * Performs deserialization through Mutator instance.
     *
     * @param  mixed  $value
     * @param  string  $key
     * @return mixed
     */
    protected function unserializeThruMutator(Mutator $mutator, $value, $key, array $args, JsonWebToken $jwt)
    {
        return $mutator->unserialize($value, $key, $args, $jwt);
    }
}
