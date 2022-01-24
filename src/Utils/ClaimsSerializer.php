<?php

namespace LittleApps\LittleJWT\Utils;

use DateTime;

use Illuminate\Support\Carbon;

class ClaimsSerializer
{
    /**
     * Serializes claim value for JWT.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    public static function serialize($key, $value)
    {
        if (in_array($key, static::getClaimKeysForTimestamps())) {
            $dateTime = ($value instanceof DateTime) ? $value : Carbon::parse($value);

            return $dateTime->getTimestamp();
        }

        return (string) $value;
    }

    /**
     * Unserializes claim value back to original.
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public static function unserialize($key, $value)
    {
        if (in_array($key, static::getClaimKeysForTimestamps())) {
            return Carbon::createFromTimestamp($value);
        }

        return $value;
    }

    /**
     * Gets the claim keys that are timestamps.
     *
     * @return array
     */
    protected static function getClaimKeysForTimestamps()
    {
        return config('littlejwt.claims.timestamps', []);
    }
}
