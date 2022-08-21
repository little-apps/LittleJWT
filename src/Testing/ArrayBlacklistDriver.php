<?php

namespace LittleApps\LittleJWT\Testing;

use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Blacklist\Drivers\AbstractDriver;
use Illuminate\Support\Carbon;

class ArrayBlacklistDriver extends AbstractDriver
{
    const DEFAULT_TTL = 0;

    protected $blacklist;

    public function __construct()
    {
        $this->blacklist = collect();
    }

    /**
     * Checks if JWT is blacklisted.
     *
     * @param JWT $jwt
     * @return bool True if blacklisted.
     */
    public function isBlacklisted(JWT $jwt)
    {
        $expires = $this->blacklist->get($this->getUniqueId($jwt));

        return !is_null($expires) && ! $this->isExpired($expires);
    }

    /**
     * Blacklists a JWT.
     *
     * @param JWT $jwt
     * @param int $ttl Length of time (in seconds) a JWT is blacklisted (0 means forever). If negative, the default TTL is used. (default: -1)
     * @return $this
     */
    public function blacklist(JWT $jwt, $ttl = -1)
    {
        $ttl = $ttl >= 0 ? $ttl : static::DEFAULT_TTL;

        $this->blacklist->put($this->getUniqueId($jwt), $ttl > 0 ? now()->addSeconds($ttl) : Carbon::maxValue());

        return $this;
    }

    /**
     * Cleanup blacklist.
     *
     * @return $this
     */
    public function purge()
    {
        $this->blacklist = $this->blacklist->filter(fn ($value) => !$this->isExpired($value));

        return $this;
    }

    /**
     * Gets the blacklist
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBlacklist() {
        return collect($this->blacklist);
    }

    /**
     * Checks if date/time is expired.
     *
     * @param Carbon $expires
     * @return boolean True if date/time has past.
     */
    protected function isExpired(Carbon $expires) {
        return now()->isAfter($expires);
    }
}
