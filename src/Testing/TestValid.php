<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\ForwardsCalls;


use LittleApps\LittleJWT\Validation\Valid;

class TestValid
{
    use ForwardsCalls;

    protected $app;
    protected $valid;

    public function __construct(Application $app, Valid $valid)
    {
        $this->app = $app;
        $this->valid = $valid;
    }

    /**
     * Builds a Validator
     *
     * @return Validator
     */
    protected function buildValidator()
    {
        $validator = $this->valid->buildValidator();

        return new TestValidator($this->app, $validator);
    }

    /**
     * Handle dynamic calls into macros or pass missing methods to the base valid instance.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->forwardCallTo($this->valid, $method, $args);
    }
}
