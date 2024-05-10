<?php

namespace LittleApps\LittleJWT\JWT;

use LittleApps\LittleJWT\Build\Sign;

/**
 * Represents a JSON Web Token (JWT).
 * This is (and isn't) an immutable class (headers, payload, and signature cannot be modified after instance is created).
 * TODO: Make class final
 */
class JsonWebToken
{
    /**
     * Sign instance to use to sign this JWT.
     *
     * @var Sign
     */
    protected $sign;

    /**
     * Header claim manager.
     *
     * @var ClaimManager
     */
    protected $headers;

    /**
     * Payload claim manager.
     *
     * @var ClaimManager
     */
    protected $payload;

    /**
     * Creates an instance that represents a JWT.
     *
     * @param  ClaimManager  $headers  Headers
     * @param  ClaimManager  $payload  Payload
     */
    public function __construct(Sign $sign, ClaimManager $headers, ClaimManager $payload)
    {
        $this->sign = $sign;
        $this->headers = $headers;
        $this->payload = $payload;
    }

    /**
     * Gets the header claims for the JWT.
     *
     * @return ClaimManager
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Gets the payload claims for the JWT.
     *
     * @return ClaimManager
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Signs this JWT.
     *
     * @return SignedJsonWebToken
     */
    public function sign()
    {
        return $this->sign->sign($this);
    }

    /**
     * Translates JWT to string form
     *
     * @return string [header].[payload]
     */
    public function __toString()
    {
        $parts = [
            (string) $this->getHeaders(),
            (string) $this->getPayload(),
        ];

        return implode('.', $parts);
    }
}
