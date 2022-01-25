<?php

namespace LittleApps\LittleJWT\Build\Builders;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Buildable;

class StackBuilder implements Buildable
{
    protected $stack;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    public function build(Builder $builder)
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
