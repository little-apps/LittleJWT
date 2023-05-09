<?php

namespace LittleApps\LittleJWT\JWT;

use LittleApps\LittleJWT\Factories\ClaimManagerBuilder;

/**
 * Represents a JSON Web Token (JWT).
 * This is an immutable class (headers, payload, and signature cannot be modified after instance is created).
 */
class JsonWebToken
{
    /**
     * Header claim manager.
     *
     * @var array
     */
    protected $headers;

    /**
     * Payload claim manager.
     *
     * @var array
     */
    protected $payload;

    /**
     * Creates an instance that represents a JWT.
     *
     * @param array $headers Headers
     * @param array $payload Payload
     * @param string $signature Signature (as raw bytes)
     */
    public function __construct(array $headers, array $payload)
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
        return $this->builder()->buildClaimManagerForHeader($this->headers);
    }

    /**
     * Gets the payload claims for the JWT.
     *
     * @return ClaimManager
     */
    public function getPayload()
    {
        return $this->builder()->buildClaimManagerForPayload($this->payload);
    }

    /**
     * Gets the Claim Manager Builder
     *
     * @return ClaimManagerBuilder
     */
    protected function builder() {
        return new ClaimManagerBuilder;
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
