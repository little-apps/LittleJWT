<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use LittleApps\LittleJWT\Concerns\ExtractsMutators;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Buildable;

class StackBuildable
{
    use ExtractsMutators;

    /**
     * Buildables to call.
     *
     * @var list<object|callable(Builder): void>
     */
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    /**
     * Gets the mutators for all buildables in stack.
     *
     * @return array{'header': array, 'payload': array} [
     *      'header' => [],
     *      'payload' => []
     * ]
     */
    public function getMutators()
    {
        $mutators = [];

        foreach ($this->stack as $callback) {
            if ($this->hasMutators($callback)) {
                $mutators = array_merge_recursive($mutators, $this->extractMutators($callback));
            }
        }

        return $mutators;
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
