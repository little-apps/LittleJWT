<?php

namespace LittleApps\LittleJWT\Facades;

use Illuminate\Support\Facades\Facade;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;

class Blacklist extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BlacklistManager::class;
    }
}
