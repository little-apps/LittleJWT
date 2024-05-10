<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Support\Facades\App;

class ValidatableBuilder
{
    /**
     * Resolves a validatable using either an alias or class name.
     *
     * @param  string  $key  Validatable alias or fully qualified class name.
     * @return \LittleApps\LittleJWT\Contracts\Validatable
     *
     * @static
     */
    public static function resolve(string $key)
    {
        $validatable = App::make(class_exists($key) ? $key : "littlejwt.validatables.{$key}");

        return $validatable;
    }
}
