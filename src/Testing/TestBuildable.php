<?php

namespace LittleApps\LittleJWT\Testing;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Mutate\Mutators;

class TestBuildable
{
    /**
     * Buildable callback.
     *
     * @var callable(Builder, Mutators): void
     */
    protected $callback;

    /**
     * Initializes test buidable
     *
     * @param callable(Builder, Mutators): void $callback Buildable callback.
     * @param array $mutators
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Performs the test buildable
     *
     * @param Builder $builder
     * @param Mutators $mutators
     * @return void
     */
    public function __invoke(Builder $builder, Mutators $mutators)
    {
        call_user_func($this->callback, $builder, $mutators);
    }
}
