<?php

namespace LittleApps\LittleJWT\JWT;

use LittleApps\LittleJWT\Utils\Base64Encoder;

class JWT
{
    protected $headers;
    protected $payload;
    protected $signature;

    /**
     * Creates an instance that represents a JWT.
     *
     * @param ClaimManager $headers Headers
     * @param ClaimManager $payload Payload
     * @param string $signature Signature (as raw bytes)
     */
    public function __construct(ClaimManager $headers, ClaimManager $payload, $signature)
    {
        $this->headers = $headers;
        $this->payload = $payload;
        $this->signature = $signature;
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
     * Gets the signature for the JWT (as raw bytes).
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    public function __toString()
    {
        $parts = [
            (string) $this->getHeaders(),
            (string) $this->getPayload(),
            Base64Encoder::encode($this->getSignature()),
        ];

        return implode('.', $parts);
    }
}
