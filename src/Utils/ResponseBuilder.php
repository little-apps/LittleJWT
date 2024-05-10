<?php

namespace LittleApps\LittleJWT\Utils;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class ResponseBuilder
{
    /**
     * Builds the JWT array from a JWT instance.
     *
     * @param JsonWebToken $jwt
     * @return array
     */
    public static function buildFromJwt(JsonWebToken $jwt)
    {
        $expires = $jwt->getPayload()->get('exp');

        // Transform to Carbon if not DateTimeInterface
        if (! ($expires instanceof DateTimeInterface)) {
            $expires = Carbon::parse($expires); // $expires maybe a number as a string
        }

        return static::buildFromToken((string) $jwt, $expires);
    }

    /**
     * Builds an array response from a token and expiry date/time.
     *
     * @param string $token
     * @param DateTimeInterface $expires
     * @return array
     */
    public static function buildFromToken(string $token, DateTimeInterface $expires)
    {
        $carbon = Carbon::instance($expires);

        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            // Seconds until JWT expires
            'expires_in' => $carbon->diffInSeconds(),
            // Date/time token expires in ISO8601 format
            'expires_at' => $carbon->format(DateTimeInterface::ATOM),
        ];

        return $data;
    }
}
