<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Build\Options;
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
     * @return void
     */
    public function __invoke(Options $options)
    {
        foreach ($this->stack as $callback) {
            if (is_callable($callback)) {
                $callback($options);
            } elseif (is_object($callback) && $callback instanceof Buildable) {
                $callback->build($options);
            }
        }
    }
}
