<?php

namespace LittleApps\LittleJWT\Mutate;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\Exceptions\CantResolveMutator;

/**
 * Resolves definitions to Mutator instances
 */
class MutatorResolver
{
    use Macroable;

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
     */
    protected readonly Application $app;

    /**
     * Custom mutator mappings
     *
     * @var array<string, class-string<\LittleApps\LittleJWT\Contracts\Mutator>>
     */
    protected array $customMutatorsMapping = [

    ];

    /**
     * Initializes MutatorResolve instance.
     */
    public function __construct(Application $app, array $customMutatorsMapping)
    {
        $this->app = $app;
        $this->customMutatorsMapping = $customMutatorsMapping;
    }

    /**
     * Resolves mutator instance
     *
     * @param  string|Mutator  $definition  String definition or Mutator instance
     * @return array{0: Mutator, 1: array} Returns array with resolved Mutator and any arguments to pass to instance method.
     *
     * @throws CantResolveMutator Thrown if definition couldn't be resolved.
     */
    public function resolve($definition)
    {
        if ($this->isMutatorInstance($definition)) {
            $mutator = $definition;

            return [$mutator, []];
        } elseif ($this->isMutatorDefinition($definition)) {
            [$key, $args] = $this->parseMutatorDefinition($definition);

            if ($this->hasPrimitiveMutatorMapping($key)) {
                return [$this->resolveFromPrimitiveMapping($key), $args];
            } elseif ($this->hasResolveMethod($key)) {
                return [$this->resolveFromMethod($key), $args];
            } elseif ($this->hasCustomMutatorMapping($key)) {
                return [$this->resolveFromCustomMapping($key), $args];
            }
        }

        throw new CantResolveMutator($definition);
    }

    /**
     * Checks if custom resolve method exists for key.
     *
     * @return bool
     */
    protected function hasResolveMethod(string $key)
    {
        $method = 'resolve'.Str::studly($key);

        return method_exists($this, $method) || $this->hasMacro($method);
    }

    /**
     * Checks if value is a Mutator isntance.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function isMutatorInstance($value)
    {
        return is_object($value) && $value instanceof Mutator;
    }

    /**
     * Checks if value is a mutator definition.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function isMutatorDefinition($value)
    {
        return is_string($value);
    }

    /**
     * Parses a type mutation.
     *
     * @param  string  $type  Type and optional arguments seperated by a :
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
     * @param  string  $mutator
     * @return bool
     */
    protected function hasPrimitiveMutatorMapping($mutator)
    {
        return array_key_exists($mutator, static::$primitiveMutatorsMapping);
    }

    /**
     * Checks if custom mutator has mapping to class.
     *
     * @param  string  $mutator
     * @return bool
     */
    protected function hasCustomMutatorMapping($mutator)
    {
        return array_key_exists($mutator, $this->customMutatorsMapping);
    }

    /**
     * Resolves mutator from method.
     *
     * @return Mutator
     */
    protected function resolveFromMethod(string $key)
    {
        return $this->{'resolve'.Str::studly($key)}();
    }

    /**
     * Resolves mutator from custom mappings.
     *
     * @return Mutator
     */
    protected function resolveFromCustomMapping(string $key)
    {
        return $this->app->make($this->customMutatorsMapping[$key]);
    }

    /**
     * Resolves mutator from primitive mappings.
     *
     * @return Mutator
     */
    protected function resolveFromPrimitiveMapping(string $key)
    {
        return $this->app->make(static::$primitiveMutatorsMapping[$key]);
    }
}
