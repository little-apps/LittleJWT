<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JWT;

use Closure;

class Callback extends Rule {
    protected $callback;

    public function __construct($key, Closure $callback, $inHeader) {
        parent::__construct($key, $inHeader);

        $this->callback = $callback;
    }

    protected function checkClaim(JWT $jwt, $value) {
        return (bool) call_user_func($this->callback, $value, $this->key, $jwt);
    }
}
