<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Contracts\BlacklistDriver;

class Allowed extends Rule {
    protected $driver;

    public function __construct(BlacklistDriver $driver) {
        $this->driver = $driver;
    }

    public function passes(JWT $jwt) {
        return !$this->driver->isBlacklisted($jwt);
    }

    public function message() {
        return 'The JWT is not allowed.';
    }
}
