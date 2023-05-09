<?php

namespace LittleApps\LittleJWT\Mutate\Mutatables;

use LittleApps\LittleJWT\Mutate\Mutators;

class StackMutatable
{
    /**
     * Stack of callables
     *
     * @var list<callable(Mutators): void>
     */
    protected $stack;

    /**
     * Initializes Stack Mutatable
     *
     * @param list<callable(Mutators): void> $stack
     */
    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    public function __invoke(Mutators $mutators)
    {
        foreach ($this->stack as $callback) {
            $callback($mutators);
        }
    }
}
