<?php

namespace LittleApps\LittleJWT\Mutate\Mutatables;

use LittleApps\LittleJWT\Concerns\PassableThru;
use LittleApps\LittleJWT\Mutate\Mutators;

// TODO: Test me!
class StackMutatable
{
    use PassableThru;

    /**
     * Initializes Stack Mutatable
     *
     * @param list<callable(Mutators): void> $stack
     */
    public function __construct(array $stack = [])
    {
        $this->passThruStack = $stack;
    }

    /**
     * Adds mutator callback to stack.
     *
     * @param callable $callback
     * @return $this
     */
    public function mutate(callable $callback)
    {
        return $this->passThru($callback);
    }

    /**
     * Runs Mutators through stack
     *
     * @param Mutators $mutators
     * @return $this
     */
    public function __invoke(Mutators $mutators)
    {
        $this->runThru($mutators);

        return $this;
    }
}
