<?php

namespace LittleApps\LittleJWT\Concerns;

trait PassableThru
{
    protected $passThruStack = [];

    /**
     * Adds callback to pass parameter(s) through
     *
     * @param callable $callback
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
     * @param array ...$params
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
