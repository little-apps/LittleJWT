<?php

namespace LittleApps\LittleJWT\Validation\Validatables;

use LittleApps\LittleJWT\Concerns\ExtractsMutators;
use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

/**
 * Allows for multiple callbacks and validatables to be stacked on top of each other.
 */
class StackValidatable
{
    use ExtractsMutators;

    /**
     * Validatables to call
     *
     * @var list<callable(Validator): void>
     */
    protected $stack;

    /**
     * Initializes stack validatable.
     *
     * @param list<callable(Validator): void> $stack Validatables to call
     */
    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    /**
     * Gets the mutators for all validatables in stack.
     *
     * @return array{'header': array, 'payload': array} [
     *      'header' => [],
     *      'payload' => []
     * ]
     */
    public function getMutators()
    {
        $mutators = [];

        foreach ($this->stack as $callback) {
            if ($this->hasMutators($callback)) {
                $mutators = array_merge_recursive($mutators, $this->extractMutators($callback));
            }
        }

        return $mutators;
    }

    /**
     * Applies validator rules.
     *
     * @param Validator $validator
     * @return void
     */
    public function __invoke(Validator $validator)
    {
        foreach ($this->stack as $callback) {
            if (is_callable($callback)) {
                $callback($validator);
            } elseif (is_object($callback) && $callback instanceof Validatable) {
                $callback->validate($validator);
            }
        }
    }
}
