<?php

namespace LittleApps\LittleJWT\Validation\Validatables;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

/**
 * Allows for multiple callbacks and validatables to be stacked on top of each other.
 */
class StackValidatable
{
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

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
