<?php

namespace LittleApps\LittleJWT\Mutate;

use DateTimeInterface;
use Illuminate\Contracts\Foundation\Application;

use Illuminate\Support\Carbon;
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
     * @param string $key
     * @param mixed $definition Mutator definition
     * @param mixed $value The claim value.
     * @param JsonWebToken $jwt Original JWT.
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
     * @param string $key
     * @param mixed $definition Mutator definition
     * @param mixed $value The claim value.
     * @param JsonWebToken $jwt Original JWT.
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
     * @param string $key
     * @param mixed $value
     * @param string|Mutator $definition
     * @param JsonWebToken $jwt Original JWT.
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
     * @param Mutator $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param JsonWebToken $jwt
     * @return mixed
     */
    protected function serializeThruMutator(Mutator $mutator, $value, $key, array $args, JsonWebToken $jwt)
    {
        return $mutator->serialize($value, $key, $args, $jwt);
    }

    /**
     * Unserializes a claim to a type definition
     *
     * @param string $key Claim key
     * @param mixed $value Claim value
     * @param string|Mutator $definition Type definition
     * @param JsonWebToken $jwt Original JWT
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
     * @param Mutator $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param JsonWebToken $jwt
     * @return mixed
     */
    protected function unserializeThruMutator(Mutator $mutator, $value, $key, array $args, JsonWebToken $jwt)
    {
        return $mutator->unserialize($value, $key, $args, $jwt);
    }

    /**
     * Creates a Carbon instance from a value using an (optional) format.
     *
     * @param DateTimeInterface|string $value Existing DateTimeInterface or date/time formatted as a string.
     * @param string|null $format If a string, used as format in $value.
     * @return Carbon
     */
    protected function createCarbonInstance($value, $format = null)
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        return is_string($format) ? Carbon::createFromFormat($format, $value) : Carbon::parse($value);
    }

    /**
     * Parses a type mutation.
     *
     * @param string $type Type and optional arguments seperated by a :
     * @return array Array with 2 elements: The mutator type and an array of any optional arguments.
     */
    protected function parseMutatorDefinition($type)
    {
        $parts = explode(':', $type);

        $mutator = $parts[0];
        $args = isset($parts[1]) ? explode(',', $parts[1]) : [];

        return [$mutator, $args];
    }
}
