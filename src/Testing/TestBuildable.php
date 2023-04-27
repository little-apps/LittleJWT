<?php

namespace LittleApps\LittleJWT\Testing;

use LittleApps\LittleJWT\Build\Builder;

class TestBuildable
{
    protected $callback;

    protected $mutators;

    public function __construct(callable $callback, array $mutators)
    {
        $this->callback = $callback;
        $this->mutators = $mutators;
    }

    /**
     * Gets mutators
     *
     * @return array
     */
    public function getMutators()
    {
        return $this->mutators;
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
