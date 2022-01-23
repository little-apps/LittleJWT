<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JWT;

class Equals extends Rule {
    protected $expected;

    protected $strict;

    public function __construct($key, $expected, $strict = true, $inHeader = false) {
        parent::__construct($key, $inHeader);

        $this->expected = $expected;
        $this->strict = $strict;
    }

    protected function checkClaim(JWT $jwt, $value) {
        return $this->strict ? $value === $this->expected : $value == $this->expected;
    }

    protected function formatMessage() {
        return sprintf("The ':key' claim does not match expected value '%s'.", $this->expected);
    }
}
