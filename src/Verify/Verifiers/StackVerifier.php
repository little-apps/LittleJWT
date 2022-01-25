<?php

namespace LittleApps\LittleJWT\Verify\Verifiers;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Verify\Validator;

class StackVerifier implements Validatable
{
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    public function verify(Validator $verifier)
    {
        foreach ($this->stack as $callback) {
            if (is_callable($callback)) {
                $callback($verifier);
            } elseif (is_object($callback) && $callback instanceof Validatable) {
                $callback->verify($verifier);
            }
        }
    }
}
