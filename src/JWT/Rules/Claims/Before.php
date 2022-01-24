<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\JWT\JWT;

class Before extends Rule
{
    protected $leeway;

    public function __construct($key, $leeway, $inHeader)
    {
        parent::__construct($key, $inHeader);

        $this->leeway = $leeway;
    }

    protected function checkClaim(JWT $jwt, $value)
    {
        $now = Carbon::now();
        $expiry = Carbon::parse($value)->addSeconds($this->leeway);

        // Now has to be before expiry + leeway
        return $now->isBefore($expiry);
    }

    protected function formatMessage()
    {
        return "The ':key' claim is after the current date/time.";
    }
}
