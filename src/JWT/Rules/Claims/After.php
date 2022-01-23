<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JWT;

use Illuminate\Support\Carbon;

class After extends Rule {
    protected $leeway;

    public function __construct($key, $leeway, $inHeader) {
        parent::__construct($key, $inHeader);

        $this->leeway = $leeway;
    }

    protected function checkClaim(JWT $jwt, $value) {
        $now = Carbon::now();

        // Check that now (+ leeway) is after the date/time
        return $now->addSeconds($this->leeway)->isAfter(Carbon::parse($value));
    }

    protected function formatMessage()
    {
        return "The ':key' claim is is before the current date/time.";
    }
}
