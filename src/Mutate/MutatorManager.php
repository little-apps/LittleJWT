<?php

namespace LittleApps\LittleJWT\Mutate;

use DateTimeInterface;
use Illuminate\Contracts\Foundation\Application;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LittleApps\LittleJWT\Contracts\Mutator;

use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use Throwable;

/**
 * Allows for claims to be serialized and deserialized.
 * The serialization happens before the JWT is JSON encoded and the unserialization happens after the JWT is decoded.
 */
class MutatorManager
{
    /**
     * Built-in mutator types.
     *
     * @var array<string, class-string<\LittleApps\LittleJWT\Contracts\Mutator>>
     */
    protected static $primitiveMutatorsMapping = [
        'array' => Mutators\ArrayMutator::class,
        'bool' => Mutators\BoolMutator::class,
        'custom_datetime' => Mutators\CustomDateTimeMutator::class,
        'date' => Mutators\DateMutator::class,
        'datetime' => Mutators\DateTimeMutator::class,
        'decimal' => Mutators\DecimalMutator::class,
        'encrypted' => Mutators\EncryptMutator::class,
        'double' => Mutators\DoubleMutator::class,
        'float' => Mutators\DoubleMutator::class,
        'real' => Mutators\DoubleMutator::class,
        'int' => Mutators\IntegerMutator::class,
        'json' => Mutators\JsonMutator::class,
        'object' => Mutators\ObjectMutator::class,
        'timestamp' => Mutators\TimestampMutator::class,
        'model' => Mutators\ModelMutator::class,
    ];

    /**
     * Custom mutator mappings
     *
     * @var array<string, class-string<\LittleApps\LittleJWT\Contracts\Mutator>>
     */
    protected $customMutatorsMapping = [

    ];

    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    public function __construct(Application $app, array $customMapping)
    {
        $this->app = $app;
        $this->customMutatorsMapping = $customMapping;
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
     * Checks if primitive mutator has mapping to class.
     *
     * @param string $mutator
     * @return bool
     */
    protected function hasPrimitiveMutatorMapping($mutator)
    {
        return array_key_exists($mutator, static::$primitiveMutatorsMapping);
    }

    /**
     * Checks if custom mutator has mapping to class.
     *
     * @param string $mutator
     * @return bool
     */
    protected function hasCustomMutatorMapping($mutator)
    {
        return array_key_exists($mutator, $this->customMutatorsMapping);
    }

    /**
     * Checks if value is a Mutator isntance.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isMutatorInstance($value)
    {
        return is_object($value) && $value instanceof Mutator;
    }

    /**
     * Checks if value is a mutator definition.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isMutatorDefinition($value)
    {
        return is_string($value);
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
        if ($this->isMutatorInstance($definition)) {
            return $this->serializeThruMutator($definition, $value, $key, [], $jwt);
        } elseif ($this->isMutatorDefinition($definition)) {
            [$mutator, $args] = $this->parseMutatorDefinition($definition);

            if (method_exists($this, 'serializeAs' . Str::studly($mutator))) {
                return $this->{'serializeAs' . Str::studly($mutator)}($value, $key, $args, $jwt);
            } elseif ($this->hasCustomMutatorMapping($mutator)) {
                return $this->serializeAsCustomMapping($mutator, $value, $key, $args, $jwt);
            } elseif ($this->hasPrimitiveMutatorMapping($mutator)) {
                return $this->serializeAsPrimitiveMapping($mutator, $value, $key, $args, $jwt);
            }
        }

        return $value;
    }

    /**
     * Serialize claim using primitive mutator.
     *
     * @param string $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param JsonWebToken $jwt
     * @return mixed
     */
    protected function serializeAsPrimitiveMapping(string $mutator, $value, string $key, array $args, JsonWebToken $jwt)
    {
        $instance = $this->app->make(static::$primitiveMutatorsMapping[$mutator]);

        return $this->serializeThruMutator($instance, $value, $key, $args, $jwt);
    }

    /**
     * Serialize claim using custom mapped mutator.
     *
     * @param string $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param JsonWebToken $jwt
     * @return mixed
     */
    protected function serializeAsCustomMapping(string $mutator, $value, string $key, array $args, JsonWebToken $jwt)
    {
        $instance = $this->app->make($this->customMutatorsMapping[$mutator]);

        return $this->serializeThruMutator($instance, $value, $key, $args, $jwt);
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
        if ($this->isMutatorInstance($definition)) {
            return $this->unserializeThruMutator($definition, $value, $key, [], $jwt);
        } elseif ($this->isMutatorDefinition($definition)) {
            [$mutator, $args] = $this->parseMutatorDefinition($definition);

            if (method_exists($this, 'unserializeAs' . Str::studly($mutator))) {
                return $this->{'unserializeAs' . Str::studly($mutator)}($value, $key, $args, $jwt);
            } elseif ($this->hasCustomMutatorMapping($mutator)) {
                return $this->unserializeAsCustomMapping($mutator, $value, $key, $args, $jwt);
            } elseif ($this->hasPrimitiveMutatorMapping($mutator)) {
                return $this->unserializeAsPrimitiveMapping($mutator, $value, $key, $args, $jwt);
            }

        }

        return $value;
    }

    /**
     * Unserialize claim using mapped primitive mutator.
     *
     * @param string $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param array $claims All other claims
     * @return mixed
     */
    protected function unserializeAsPrimitiveMapping(string $mutator, $value, string $key, array $args, JsonWebToken $jwt)
    {
        $instance = $this->app->make(static::$primitiveMutatorsMapping[$mutator]);

        return $this->unserializeThruMutator($instance, $value, $key, $args, $jwt);
    }

    /**
     * Unserialize claim using mapped custom mutator.
     *
     * @param string $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param array $claims All other claims
     * @return mixed
     */
    protected function unserializeAsCustomMapping(string $mutator, $value, string $key, array $args, JsonWebToken $jwt)
    {
        $instance = $this->app->make($this->customMutatorsMapping[$mutator]);

        return $this->unserializeThruMutator($instance, $value, $key, $args, $jwt);
    }

    /**
     * Performs deserialization through Mutator instance.
     *
     * @param Mutator $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param array $claims All other claims
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
