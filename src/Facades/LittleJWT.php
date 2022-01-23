<?php

namespace LittleApps\LittleJWT\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LittleApps\LittleJWT\LittleJWT
 */
class LittleJWT extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'littlejwt';
    }
}
