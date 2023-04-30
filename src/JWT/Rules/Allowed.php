<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\Contracts\BlacklistDriver;
use LittleApps\LittleJWT\JWT\JWT;

class Allowed extends Rule
{
    /**
     * Blacklist driver
     *
     * @var BlacklistDriver
     */
    protected $driver;

    public function __construct(BlacklistDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @inheritDoc
     */
    public function passes(JWT $jwt)
    {
        return ! $this->driver->isBlacklisted($jwt);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'The JWT is not allowed.';
    }
}
