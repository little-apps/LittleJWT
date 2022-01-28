<?php

namespace LittleApps\LittleJWT\JWT\Concerns;

use DateTimeInterface;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

/**
 * Allows for claims to be serialized and deserialized.
 * The claims are still sent through json_encode/json_decode.
 * The purpose of this trait is to allow for further control over the serialization/deserialization process.
 */
trait MutatesClaims
{
    protected static $dateFormat = 'Y-m-d';
    protected static $dateTimeFormat = DateTimeInterface::ISO8601;

    /**
     * Built-in mutator types.
     *
     * @var array
     */
    protected static $primitiveMutatorTypes = [
        'array',
        'bool',
        'custom_datetime',
        'date',
        'datetime',
        'decimal',
        'encrypted',
        'double',
        'float',
        'real',
        'int',
        'json',
        'object',
        'timestamp'
    ];

    /**
     * Claim keys to mutate.
     *
     * @var array
     */
    protected $mutators = [];

    /**
     * Serializes claim value for JWT.
     *
     * @param string $key
     * @param mixed $value The claim value before it's processed through json_encode.
     * @return mixed
     */
    public function serialize($key, $value)
    {
        if ($this->hasMutator($key)) {
            $value = $this->serializeAs($key, $value, $this->getMutatorDefinition($key));
        }

        return $value;
    }

    /**
     * Unserializes claim value back to original.
     * The claim value will still be sent through json_decode after.
     *
     * @param string $key
     * @param mixed $value The claim value after it's sent through json_decode.
     * @return mixed
     */
    public function unserialize($key, $value)
    {
        if ($this->hasMutator($key)) {
            $value = $this->unserializeAs($key, $value, $this->getMutatorDefinition($key));
        }

        return $value;
    }

    /**
     * Gets the mutator definitions.
     *
     * @return array
     */
    protected function getMutatorDefinitions() {
        return $this->mutators;
    }

    /**
     * Gets the mutator definition for key.
     *
     * @param string $key
     * @return string
     */
    protected function getMutatorDefinition($key) {
        return $this->mutators[$key];
    }

    /**
     * Checks if mutator exists for key.
     *
     * @param string $key
     * @return boolean
     */
    protected function hasMutator($key)
    {
        return isset($this->mutators[$key]);
    }

    /**
     * Checks if mutator is primitive/built-in.
     *
     * @param string $mutator
     * @return boolean
     */
    protected function isPrimitiveMutator($mutator)
    {
        return in_array($mutator, static::$primitiveMutatorTypes);
    }

    /**
     * Serializes claim for storing in JWT.
     *
     * @param string $key
     * @param mixed $value
     * @param string $definition
     * @return string
     */
    protected function serializeAs($key, $value, $definition)
    {
        [$mutator, $args] = $this->parseMutatorDefinition($definition);

        if (method_exists($this, 'serializeAs' . Str::studly($mutator)))
            return $this->{'serializeAs' . Str::studly($mutator)}($value, $key, $args);
        else if ($this->isPrimitiveMutator($mutator))
            return $this->serializeAsPrimitive($value, $mutator, $args);
        else
            return $value;
    }

    /**
     * Serializes claim using primitive mutator.
     *
     * @param mixed $value
     * @param string $mutator
     * @param array $args
     * @return mixed
     */
    protected function serializeAsPrimitive($value, $mutator, $args) {
        switch ($mutator) {
            case 'custom_datetime': {
                [$format] = $args;

                return $this->createCarbonInstance($value, $format)->format($format);
            }
            case 'date': {
                return $this->createCarbonInstance($value)->startOfDay()->format(static::$dateFormat);
            }
            case 'datetime': {
                return $this->createCarbonInstance($value)->format(static::$dateTimeFormat);
            }
            case 'decimal': {
                [$decimals] = $args;

                return number_format($value, $decimals ?? 0, '.', '');
            }
            case 'encrypted': {
                return Crypt::encrypt($value);
            }
            case 'double':
            case 'float':
            case 'real': {
                if (is_infinite($value))
                    $value = $value !== -INF ? 'Infinity' : '-Infinity';
                else if (is_nan($value))
                    $value = 'NaN';
                else
                    $value = (string) $value;

                return $value;
            }
            case 'json':
            case 'object': {
                return json_encode($value);
            }
            case 'timestamp': {
                return $this->createCarbonInstance($value)->getTimestamp();
            }

            default: {
                return $value;
            }
        }
    }

    /**
     * Unserializes a claim to a type definition
     *
     * @param string $key Claim key
     * @param mixed $value Claim value
     * @param string $type Type definition
     * @return mixed
     */
    protected function unserializeAs($key, $value, $type)
    {
        [$mutator, $args] = $this->parseMutatorDefinition($type);

        if (method_exists($this, 'unserializeAs' . Str::studly($mutator)))
            return $this->{'unserializeAs' . Str::studly($mutator)}($value, $key, $args);
        else if ($this->isPrimitiveMutator($mutator))
            return $this->unserializeAsPrimitive($value, $mutator, $args);
        else
            return $value;
    }

    /**
     * Unserializes claim to a primitive/built-in type definition.
     *
     * @param mixed $value Claim value
     * @param string $mutator Primitive mutator to use.
     * @param array $args Any arguments for the mutator.
     * @return mixed
     */
    protected function unserializeAsPrimitive($value, $mutator, $args) {
        switch ($mutator) {
            case 'array': {
                return (array) $value;
            }
            case 'bool': {
                return (bool) $value;
            }
            case 'custom_datetime': {
                [$format] = $args;

                return $this->createCarbonInstance($value, $format);
            }
            case 'date': {
                return $this->createCarbonInstance($value, static::$dateFormat)->startOfDay();
            }
            case 'datetime': {
                return $this->createCarbonInstance($value, static::$dateTimeFormat);
            }
            case 'decimal': {
                return (double) $value;
            }
            case 'encrypted': {
                return Crypt::decrypt($value);
            }
            case 'double':
            case 'float':
            case 'real': {
                if ($value === 'Infinity')
                    $value = INF;
                else if ($value === '-Infinity')
                    $value = -INF;
                else if ($value === 'NaN')
                    $value = NAN;
                else
                    $value = (double) $value;

                return $value;
            }
            case 'int': {
                return (int) $value;
            }
            case 'json': {
                return json_decode($value, true);
            }
            case 'object': {
                return json_decode($value, false);
            }
            case 'timestamp': {
                return Carbon::createFromTimestamp($value);
            }
            default: {
                return $value;
            }
        }
    }

    /**
     * Creates a Carbon instance from a value using an (optional) format.
     *
     * @param DateTimeInterface|string $value Existing DateTimeInterface or date/time formatted as a string.
     * @param string|null $format If a string, used as format in $value.
     * @return Carbon
     */
    protected function createCarbonInstance($value, $format = null) {
        if ($value instanceof DateTimeInterface)
            return Carbon::instance($value);

        return is_string($format) ? Carbon::parse($value) : Carbon::createFromFormat($format, $value);
    }

    /**
     * Parses a type mutation.
     *
     * @param string $type Type and optional arguments seperated by a :
     * @return array Array with 2 elements: The mutator type and an array of any optional arguments.
     */
    protected function parseMutatorDefinition($type) {
        $parts = explode(':', $type);

        $mutator = $parts[0];
        $args = isset($parts[1]) ? explode(',', $parts[1]) : [];

        return [$mutator, $args];
    }
}
