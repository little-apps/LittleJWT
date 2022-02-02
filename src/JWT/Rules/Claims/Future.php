<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\JWT\JWT;

class Future extends Rule
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

        // The expiry + leeway has to be after now.
        return $expiry->isAfter($now);
    }

    protected function formatMessage()
    {
        return "The ':key' claim date/time has past.";
    }
}
