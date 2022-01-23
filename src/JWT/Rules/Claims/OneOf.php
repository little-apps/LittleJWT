<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JWT;

class OneOf extends Rule {
    protected $haystack;

    protected $strict;

    public function __construct($key, array $haystack, $strict = true, $inHeader = false) {
        parent::__construct($key, $inHeader);

        $this->haystack = $haystack;
        $this->strict = $strict;
    }

    protected function checkClaim(JWT $jwt, $value) {
        return in_array($value, $this->haystack, $this->strict);
    }

    protected function formatMessage() {
        return "The ':key' claim is not one of expected values.";
    }
}
