<?php

namespace LittleApps\LittleJWT\JWT;

use DateTimeInterface;

use Illuminate\Contracts\Foundation\Application;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LittleApps\LittleJWT\Contracts\Mutator;

/**
 * Allows for claims to be serialized and deserialized.
 * The serialization happens before the JWT is JSON encoded and the unserialization happens after the JWT is decoded.
 */
class MutatorManager
{
    /**
     * Built-in mutator types.
     *
     * @var array
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
     * Application container
     *
     * @var Application
     */
    protected $app;

    /**
     * Claim keys to mutate.
     *
     * @var list<string>
     */
    protected $mutators = [];

    public function __construct(Application $app, array $mutators)
    {
        $this->app = $app;
        $this->mutators = $mutators;
    }

    /**
     * Serializes claim value for JWT.
     *
     * @param string $key
     * @param mixed $value The claim value before it's processed through json_encode.
     * @param array $claims All of the claims.
     * @return mixed
     */
    public function serialize($key, $value, array $claims)
    {
        if ($this->hasMutator($key)) {
            $value = $this->serializeAs($key, $value, $this->getMutatorDefinition($key), $claims);
        }

        return $value;
    }

    /**
     * Unserializes claim value back to original.
     * The claim value will still be sent through json_decode after.
     *
     * @param string $key
     * @param mixed $value The claim value after it's sent through json_decode.
     * @param array $claims All other claims
     * @return mixed
     */
    public function unserialize($key, $value, array $claims)
    {
        if ($this->hasMutator($key)) {
            $value = $this->unserializeAs($key, $value, $this->getMutatorDefinition($key), $claims);
        }

        return $value;
    }

    /**
     * Gets the mutator definitions.
     *
     * @return array
     */
    protected function getMutatorDefinitions()
    {
        return $this->mutators;
    }

    /**
     * Gets the mutator definition for key.
     *
     * @param string $key
     * @return string
     */
    protected function getMutatorDefinition($key)
    {
        return $this->mutators[$key];
    }

    /**
     * Checks if mutator exists for key.
     *
     * @param string $key
     * @return bool
     */
    protected function hasMutator($key)
    {
        return isset($this->mutators[$key]);
    }

    /**
     * Checks if mutator has mapping to class.
     *
     * @param string $mutator
     * @return bool
     */
    protected function hasMutatorMapping($mutator)
    {
        return array_key_exists($mutator, static::$primitiveMutatorsMapping);
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
     * @param array $claims All claims
     * @return string
     */
    protected function serializeAs($key, $value, $definition, array $claims)
    {
        if ($this->isMutatorInstance($definition)) {
            return $this->serializeThruMutator($definition, $value, $key, [], $claims);
        } elseif ($this->isMutatorDefinition($definition)) {
            [$mutator, $args] = $this->parseMutatorDefinition($definition);

            if (method_exists($this, 'serializeAs' . Str::studly($mutator))) {
                return $this->{'serializeAs' . Str::studly($mutator)}($value, $key, $args);
            } elseif ($this->hasMutatorMapping($mutator)) {
                return $this->serializeAsMapping($mutator, $value, $key, $args, $claims);
            }
        }

        return $value;
    }

    /**
     * Serialize claim using mapped mutator.
     *
     * @param string $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @return mixed
     */
    protected function serializeAsMapping(string $mutator, $value, string $key, array $args, array $claims)
    {
        $instance = $this->app->make(static::$primitiveMutatorsMapping[$mutator]);

        return $this->serializeThruMutator($instance, $value, $key, $args, $claims);
    }

    /**
     * Performs serialization through Mutator instance.
     *
     * @param Mutator $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param array $claims
     * @return mixed
     */
    protected function serializeThruMutator(Mutator $mutator, $value, $key, array $args, array $claims)
    {
        return $mutator->serialize($value, $key, $args, $claims);
    }

    /**
     * Unserializes a claim to a type definition
     *
     * @param string $key Claim key
     * @param mixed $value Claim value
     * @param string|Mutator $definition Type definition
     * @param array $claims All other claims
     * @return mixed
     */
    protected function unserializeAs($key, $value, $definition, array $claims)
    {
        if ($this->isMutatorInstance($definition)) {
            return $this->unserializeThruMutator($definition, $value, $key, [], $claims);
        } elseif ($this->isMutatorDefinition($definition)) {
            [$mutator, $args] = $this->parseMutatorDefinition($definition);

            if (method_exists($this, 'unserializeAs' . Str::studly($mutator))) {
                return $this->{'unserializeAs' . Str::studly($mutator)}($value, $key, $args);
            } elseif ($this->hasMutatorMapping($mutator)) {
                return $this->unserializeAsMapping($mutator, $value, $key, $args, $claims);
            }

        }

        return $value;
    }

    /**
     * Unserialize claim using mapped mutator.
     *
     * @param string $mutator
     * @param mixed $value
     * @param string $key
     * @param array $args
     * @param array $claims All other claims
     * @return mixed
     */
    protected function unserializeAsMapping(string $mutator, $value, string $key, array $args, array $claims)
    {
        $instance = $this->app->make(static::$primitiveMutatorsMapping[$mutator]);

        return $this->unserializeThruMutator($instance, $value, $key, $args, $claims);
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
    protected function unserializeThruMutator(Mutator $mutator, $value, $key, array $args, array $claims)
    {
        return $mutator->unserialize($value, $key, $args, $claims);
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
