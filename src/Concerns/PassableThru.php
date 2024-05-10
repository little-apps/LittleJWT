<?php

namespace LittleApps\LittleJWT\Concerns;

trait PassableThru
{
    /**
     * Callbacks
     *
     * @var array<callable>
     */
    protected $passThruStack = [];

    /**
     * Adds callback to pass parameter(s) through
     *
     * @return $this
     */
    protected function passThru(callable $callback)
    {
        array_push($this->passThruStack, $callback);

        return $this;
    }

    /**
     * Sends parameters through callbacks.
     *
     * @param  array  ...$params
     * @return $this
     */
    protected function runThru(...$params)
    {
        foreach ($this->passThruStack as $callback) {
            $callback(...$params);
        }

        return $this;
    }
}
