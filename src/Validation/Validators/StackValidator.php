<?php

namespace LittleApps\LittleJWT\Validation\Validators;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

class StackValidator implements Validatable
{
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    public function validate(Validator $validator)
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
