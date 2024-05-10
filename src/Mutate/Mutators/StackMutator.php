<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use LittleApps\LittleJWT\Concerns\PassableThru;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class StackMutator implements Mutator
{
    use PassableThru;

    /**
     * Adds mutator to stack.
     *
     * @param Mutator $mutator
     * @return $this
     */
    public function mutator(Mutator $mutator)
    {
        return $this->passThru(function ($method, ...$args) use ($mutator) {
            return $mutator->$method(...$args);
        });
    }

    /**
     * @inheritDoc
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        foreach ($this->passThruStack as $callback) {
            $value = $callback('serialize', $value, $key, $args, $jwt);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        /**
         * Stack is reversed when unserializing so values go through
         * first -> last when serializing and last -> first when unserializing.
         */
        foreach (array_reverse($this->passThruStack) as $callback) {
            $value = $callback('unserialize', $value, $key, $args, $jwt);
        }

        return $value;
    }
}
