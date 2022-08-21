<?php

namespace LittleApps\LittleJWT\Facades;

use Illuminate\Support\Facades\Facade;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Testing\ArrayBlacklistDriver;

class Blacklist extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return \Illuminate\Support\Testing\Fakes\EventFake
     */
    public static function fake()
    {
        $instance = static::partialMock()->shouldReceive('getDefaultDriver')->andReturn('array');

        static::extend('array', fn () => new ArrayBlacklistDriver());

        return $instance;
    }

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
