<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\JWT\JWT;

class Past extends Rule
{
    protected $leeway;

    public function __construct($key, $leeway, $inHeader)
    {
        parent::__construct($key, $inHeader);

        $this->leeway = $leeway;
    }

    protected function checkClaim(JWT $jwt, $value)
    {
        $now = Carbon::now()->addSeconds($this->leeway);
        $dateTime = Carbon::parse($value);

        // Check that the date/time is before now (+/- leeway)
        return $dateTime->isBefore($now);
    }

    protected function formatMessage()
    {
        return "The ':key' claim is is before the current date/time.";
    }
}
