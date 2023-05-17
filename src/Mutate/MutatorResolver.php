<?php

namespace LittleApps\LittleJWT\Mutate;

use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\Exceptions\CantResolveMutator;

/**
 * Resolves definitions to Mutator instances
 */
class MutatorResolver
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
     * Application container
     *
     * @var Application
     */
    protected $app;

    /**
     * Custom mutator mappings
     *
     * @var array<string, class-string<\LittleApps\LittleJWT\Contracts\Mutator>>
     */
    protected $customMutatorsMapping = [

    ];

    /**
     * Initializes MutatorResolve instance.
     *
     * @param Application $app
     * @param array $customMutatorsMapping
     */
    public function __construct(Application $app, array $customMutatorsMapping)
    {
        $this->app = $app;
        $this->customMutatorsMapping = $customMutatorsMapping;
    }

    /**
     * Resolves mutator instance
     *
     * @param string|Mutator $definition String definition or Mutator instance
     * @return array{0: Mutator, 1: array} Returns array with resolved Mutator and any arguments to pass to instance method.
     * @throws CantResolveMutator Thrown if definition couldn't be resolved.
     */
    public function resolve($definition)
    {
        if ($this->isMutatorInstance($definition)) {
            $mutator = $definition;

            return [$mutator, []];
        } elseif ($this->isMutatorDefinition($definition)) {
            [$key, $args] = $this->parseMutatorDefinition($definition);

            if ($this->hasCustomMutatorMapping($key)) {
                return [$this->resolveFromCustomMapping($key), $args];
            } elseif ($this->hasPrimitiveMutatorMapping($key)) {
                return [$this->resolveFromPrimitiveMapping($key), $args];
            }
        }

        throw new CantResolveMutator($definition);
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
     * Parses a type mutation.
     *
     * @param string $type Type and optional arguments seperated by a :
     * @return array{0: string, 1: array} Array with 2 elements: The mutator key and an array of any optional arguments.
     */
    protected function parseMutatorDefinition($type)
    {
        $parts = explode(':', $type);

        $mutator = $parts[0];
        $args = isset($parts[1]) ? explode(',', $parts[1]) : [];

        return [$mutator, $args];
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
     * Resolves mutator from custom mappings.
     *
     * @param string $key
     * @return Mutator
     */
    protected function resolveFromCustomMapping(string $key)
    {
        return $this->app->make($this->customMutatorsMapping[$key]);
    }

    /**
     * Resolves mutator from primitive mappings.
     *
     * @param string $key
     * @return Mutator
     */
    protected function resolveFromPrimitiveMapping(string $key)
    {
        return $this->app->make(static::$primitiveMutatorsMapping[$key]);
    }
}
