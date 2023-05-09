<?php

namespace LittleApps\LittleJWT\Blacklist\Drivers;

use Illuminate\Cache\CacheManager;

use LittleApps\LittleJWT\Concerns\JWTHelpers;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class CacheDriver extends AbstractDriver
{
    use JWTHelpers;

    /**
     * Cache manager.
     *
     * @var CacheManager
     */
    protected $manager;

    /**
     * Options for caching.
     *
     * @var array
     */
    protected $options;

    public function __construct(CacheManager $manager, array $options)
    {
        $this->manager = $manager;
        $this->options = $options;
    }

    /**
     * Checks if JWT is blacklisted.
     *
     * @param JsonWebToken $jwt
     * @return bool True if blacklisted.
     */
    public function isBlacklisted(JsonWebToken $jwt)
    {
        return $this->manager->has($this->getUniqueId($jwt));
    }

    /**
     * Blacklists a JWT.
     *
     * @param JsonWebToken $jwt
     * @param int $ttl Length of time (in seconds) a JWT is blacklisted (0 means forever). If negative, the default TTL is used. (default: -1)
     * @return $this
     */
    public function blacklist(JsonWebToken $jwt, $ttl = -1)
    {
        $ttl = $ttl >= 0 ? $ttl : $this->getDefaultTtl();

        // The cache uses null to indicate it should be stored forever.
        $this->manager->put($this->getUniqueId($jwt), $jwt, $ttl > 0 ? $ttl : null);

        return $this;
    }

    /**
     * Cleanup blacklist.
     *
     * @return $this
     */
    public function purge()
    {
        return $this;
    }
}
