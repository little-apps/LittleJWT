<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\JWT\JWT;

class Future extends Rule
{
    /**
     * Leeway (in seconds) to allow date/time be in future.
     *
     * @var int
     */
    protected $leeway;

    /**
     * Intializes Future rule.
     *
     * @param string $key Claim key to check.
     * @param int $leeway Additional number of seconds to allow date/time be in future.
     * @param bool $inHeader If true, uses header claim.
     */
    public function __construct($key, $leeway, $inHeader)
    {
        parent::__construct($key, $inHeader);

        $this->leeway = $leeway;
    }

    protected function checkClaim(JWT $jwt, $value)
    {
        $now = Carbon::now();
        $dateTime = Carbon::parse($value)->addSeconds($this->leeway);

        // The expiry + leeway has to be after now.
        return $dateTime->isAfter($now);
    }

    protected function formatMessage()
    {
        return "The ':key' claim date/time has past.";
    }
}
