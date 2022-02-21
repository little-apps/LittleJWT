<?php

namespace LittleApps\LittleJWT\Concerns;

use Illuminate\Support\Facades\App;

trait ResolvesValidatables
{
    /**
     * Resolves a validatable using either an alias or class name.
     *
     * @param string $key Validatable alias or fully qualified class name.
     * @return \LittleApps\LittleJWT\Contracts\Validatable
     */
    protected function resolveValidatable(string $key)
    {
        $validatable = App::make(class_exists($key) ? $key : "littlejwt.validatables.{$key}");

        return $validatable;
    }
}
