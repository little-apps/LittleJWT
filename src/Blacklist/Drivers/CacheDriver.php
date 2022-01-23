<?php

namespace LittleApps\LittleJWT\Blacklist\Drivers;

use LittleApps\LittleJWT\JWT\JWT;

use Illuminate\Cache\CacheManager;

class CacheDriver extends AbstractDriver {
    protected $manager;

    protected $options;

    public function __construct(CacheManager $manager, array $options) {
        $this->manager = $manager;
        $this->options = $options;
    }

    /**
     * Checks if JWT is blacklisted.
     *
     * @param JWT $jwt
     * @return boolean True if blacklisted.
     */
    public function isBlacklisted(JWT $jwt) {
        return $this->manager->has($this->getUniqueId($jwt));
    }

    /**
     * Blacklists a JWT.
     *
     * @param JWT $jwt
     * @param int $ttl Length of time (in seconds) a JWT is blacklisted (0 means forever). If negative, the default TTL is used. (default: -1)
     * @return $this
     */
    public function blacklist(JWT $jwt, $ttl = -1) {
        $ttl = $ttl >= 0 ? $ttl : $this->options['ttl'];

        // The cache uses null to indicate it should be stored forever.
        $this->manager->put($this->getUniqueId($jwt), $jwt, $ttl > 0 ? $ttl : null);

        return $this;
    }

    /**
     * Cleanup blacklist.
     *
     * @return $this
     */
    public function purge() {
        return $this;
    }
}
