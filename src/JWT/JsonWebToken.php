<?php

namespace LittleApps\LittleJWT\JWT;

/**
 * Represents a JSON Web Token (JWT).
 * This is (and isn't) an immutable class (headers, payload, and signature cannot be modified after instance is created).
 * TODO: Make class final
 */
class JsonWebToken
{
    /**
     * Header claim manager.
     */
    protected readonly ClaimManager $headers;

    /**
     * Payload claim manager.
     */
    protected readonly ClaimManager $payload;

    /**
     * Creates an instance that represents a JWT.
     *
     * @param  ClaimManager  $headers  Headers
     * @param  ClaimManager  $payload  Payload
     */
    public function __construct(ClaimManager $headers, ClaimManager $payload)
    {
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
