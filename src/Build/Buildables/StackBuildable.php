<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Buildable;

class StackBuildable
{
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    /**
     * Gets the mutators for all buildables in stack.
     *
     * @return array [
     *      'header' => [],
     *      'payload' => []
     * ]
     */
    public function getMutators() {
        $mutators = [];

        foreach ($this->stack as $callback) {
            if (method_exists($callback, 'getMutators')) {
                $mutators = array_merge_recursive($mutators, $callback->getMutators());
            }
        }

        return $mutators;
    }

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
