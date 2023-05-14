<?php

namespace LittleApps\LittleJWT\JWT;

use LittleApps\LittleJWT\Build\Sign;
use LittleApps\LittleJWT\Factories\ClaimManagerBuilder;

/**
 * Represents a JSON Web Token (JWT).
 * This is an immutable class (headers, payload, and signature cannot be modified after instance is created).
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
     * @param Sign $sign
     * @param array $headers Headers
     * @param array $payload Payload
     */
    public function __construct(Sign $sign, array $headers, array $payload)
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
     * Signs this JWT.
     *
     * @return SignedJsonWebToken
     */
    public function sign()
    {
        return $this->sign->sign($this);
    }

    /**
     * Gets the Claim Manager Builder
     *
     * @return ClaimManagerBuilder
     */
    protected function builder()
    {
        return new ClaimManagerBuilder();
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
