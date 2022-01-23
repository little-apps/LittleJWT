<?php

namespace LittleApps\LittleJWT\Verify\Verifiers;

use LittleApps\LittleJWT\Contracts\Verifiable;
use LittleApps\LittleJWT\Verify\Verifier;

class StackVerifier implements Verifiable {
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    public function verify(Verifier $verifier)
    {
        foreach ($this->stack as $callback) {
            if (is_callable($callback))
                $callback($verifier);
            else if (is_object($callback) && $callback instanceof Verifiable)
                $callback->verify($verifier);
        }
    }
}
