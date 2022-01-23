<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JWT;

class SecureEquals extends Rule {
    protected $expected;

    public function __construct($key, $expected, $inHeader = false) {
        parent::__construct($key, $inHeader);

        $this->expected = $expected;
    }

    protected function checkClaim(JWT $jwt, $value) {
        return hash_equals($this->expected, $value);
    }

    protected function formatMessage() {
        return sprintf("The ':key' claim does not match expected value '%s'.", $this->expected);
    }
}
