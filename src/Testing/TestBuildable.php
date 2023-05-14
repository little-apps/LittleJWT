<?php

namespace LittleApps\LittleJWT\Testing;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Mutate\Mutators;

class TestBuildable
{
    /**
     * Buildable callback.
     *
     * @var callable(Builder): void
     */
    protected $callback;

    /**
     * Initializes test buidable
     *
     * @param callable(Builder): void $callback Buildable callback.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Performs the test buildable
     *
     * @param Builder $builder
     * @return void
     */
    public function __invoke(Builder $builder)
    {
        call_user_func($this->callback, $builder);
    }
}
