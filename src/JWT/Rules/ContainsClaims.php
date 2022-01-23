<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\JWT\JWT;

class ContainsClaims extends Rule {
    protected $expected;
    protected $inHeader;
    protected $strict;

    public function __construct(iterable $expected, $inHeader = false, $strict = false) {
        $this->expected = $expected;
        $this->inHeader = (bool) $inHeader;
        $this->strict = (bool) $strict;
    }

    public function passes(JWT $jwt) {
        $claims = $this->inHeader ? $jwt->getHeaders() : $jwt->getPayload();

        $found = 0;

        foreach ($this->expected as $key) {
            if ($claims->has($key))
                $found++;
        }

        if ($this->strict) {
            // If in strict mode, only return true if # found is the exact same as the number expected and as how many claims there are.
            return $found === count($this->expected) && $found === count($claims);
        } else {
            return $found >= count($this->expected);
        }
    }

    public function message() {
        return 'The JWT did not have the expected claim keys.';
    }
}
