<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Buildable;
use LittleApps\LittleJWT\Mutate\Mutators;

class StackBuildable
{
    /**
     * Buildables to call.
     *
     * @var list<object|callable(Builder, Mutators): void>
     */
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    /**
     * Calls buildables in stack.
     *
     * @param Builder $builder
     * @return void
     */
    public function __invoke(Builder $builder)
    {
        foreach ($this->stack as $callback) {
            if (is_callable($callback)) {
                $callback($builder);
            } elseif (is_object($callback) && $callback instanceof Buildable) {
                $callback->build($builder);
            }
        }
    }
}
