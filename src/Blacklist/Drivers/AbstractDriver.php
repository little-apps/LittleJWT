<?php

namespace LittleApps\LittleJWT\Blacklist\Drivers;

use LittleApps\LittleJWT\Contracts\BlacklistDriver;

abstract class AbstractDriver implements BlacklistDriver
{
    /**
     * Gets default TTL to use for blacklist
     *
     * @return int TTL in seconds (0 means forever)
     */
    protected function getDefaultTtl()
    {
        return config('littlejwt.blacklist.ttl', 0);
    }
}
