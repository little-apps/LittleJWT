<?php

namespace LittleApps\LittleJWT\Blacklist\Drivers;

use LittleApps\LittleJWT\JWT\JWT;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DatabaseDriver extends AbstractDriver {
    protected $options;

    public function __construct(array $options) {
        $this->options = $options;
    }

    /**
     * Checks if JWT is blacklisted.
     *
     * @param JWT $jwt
     * @return boolean True if blacklisted.
     */
    public function isBlacklisted(JWT $jwt) {
        return
            DB::table($this->getTableName())
                ->where($this->getIdentifierColumnName(), $this->getUniqueId($jwt))
                ->whereDate($this->getIdentifierExpiryName(), '<=', Carbon::now())
                    ->exists();
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

        DB::table($this->getTableName())->insert([
            $this->getIdentifierColumnName() => $this->getUniqueId($jwt),
            $this->getIdentifierExpiryName() => $ttl > 0 ? Carbon::now()->addSeconds($ttl) : null
        ]);

        return $this;
    }

    /**
     * Cleanup blacklist.
     *
     * @return $this
     */
    public function purge() {
        DB::table($this->getTableName())
            ->whereDate($this->getIdentifierExpiryName(), '>', Carbon::now())
                ->delete();

        return $this;
    }

    /**
     * Gets the name of the table containing the blacklisted JWTs.
     *
     * @return string
     */
    protected function getTableName() {
        return $this->options['table'];
    }

    /**
     * Gets the name of the identifier column in the table.
     *
     * @return string
     */
    protected function getIdentifierColumnName() {
        return $this->options['columns']['identifier'];
    }

    /**
     * Gets the name of the expiry column in the table.
     *
     * @return string
     */
    protected function getIdentifierExpiryName() {
        return $this->options['columns']['expiry'];
    }
}
