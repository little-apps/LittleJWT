<?php

namespace LittleApps\LittleJWT\Blacklist\Drivers;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;
use LittleApps\LittleJWT\Concerns\JWTHelpers;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class DatabaseDriver extends AbstractDriver
{
    use JWTHelpers;

    /**
     * Options for database driver.
     *
     * @var array
     */
    protected $options;

    public function __construct(array $options)
    {
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
        return
            DB::table($this->getTableName())
                ->where($this->getIdentifierColumnName(), $this->getUniqueId($jwt))
                ->whereDate($this->getIdentifierExpiryName(), '<=', Carbon::now())
                    ->exists();
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

        DB::table($this->getTableName())->insert([
            $this->getIdentifierColumnName() => $this->getUniqueId($jwt),
            $this->getIdentifierExpiryName() => $ttl > 0 ? Carbon::now()->addSeconds($ttl) : 0,
        ]);

        return $this;
    }

    /**
     * Cleanup blacklist.
     *
     * @return $this
     */
    public function purge()
    {
        DB::table($this->getTableName())
            ->where($this->getIdentifierExpiryName(), '>', Carbon::now()->getTimestamp())
                ->delete();

        return $this;
    }

    /**
     * Gets the name of the table containing the blacklisted JWTs.
     *
     * @return string
     */
    protected function getTableName()
    {
        return $this->options['table'];
    }

    /**
     * Gets the name of the identifier column in the table.
     *
     * @return string
     */
    protected function getIdentifierColumnName()
    {
        return $this->options['columns']['identifier'];
    }

    /**
     * Gets the name of the expiry column in the table.
     *
     * @return string
     */
    protected function getIdentifierExpiryName()
    {
        return $this->options['columns']['expiry'];
    }
}
