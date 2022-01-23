<?php

namespace LittleApps\LittleJWT\Facades;

use Illuminate\Support\Facades\Facade;

class Builder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'littlejwt.builder';
    }
}
