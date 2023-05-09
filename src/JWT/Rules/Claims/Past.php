<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\JWT\JsonWebToken;

class Past extends Rule
{
    /**
     * Leeway (in seconds) to allow date/time be in past.
     *
     * @var int
     */
    protected $leeway;

    /**
     * Initializes Past rule.
     *
     * @param string $key Claim key to check.
     * @param int $leeway Additional number of seconds to allow date/time be in past.
     * @param bool $inHeader If true, uses header claim.
     */
    public function __construct($key, $leeway, $inHeader)
    {
        parent::__construct($key, $inHeader);

        $this->leeway = $leeway;
    }

    /**
     * @inheritDoc
     */
    protected function checkClaim(JsonWebToken $jwt, $value)
    {
        $now = Carbon::now()->addSeconds($this->leeway);
        $dateTime = Carbon::parse($value);

        // Check that the date/time is before now (+/- leeway)
        return $dateTime->isBefore($now);
    }

    /**
     * @inheritDoc
     */
    protected function formatMessage()
    {
        return "The ':key' claim date/time is in the future.";
    }
}
