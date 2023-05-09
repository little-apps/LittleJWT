<?php

namespace LittleApps\LittleJWT\JWT;

use LittleApps\LittleJWT\Utils\Base64Encoder;

/**
 * Represents a JSON Web Token (JWT).
 * This is an immutable class (headers, payload, and signature cannot be modified after instance is created).
 */
class SignedJsonWebToken extends JsonWebToken
{
    /**
     * Signature
     *
     * @var string
     */
    protected $signature;

    /**
     * Creates an instance that represents a JWT.
     *
     * @param array $headers Headers
     * @param array $payload Payload
     * @param string $signature Signature (as raw bytes)
     */
    public function __construct(array $headers, array $payload, string $signature)
    {
        parent::__construct($headers, $payload);

        $this->signature = $signature;
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

    /**
     * Transforms JWT to string form.
     *
     * @return string [header].[payload].[signature]
     */
    public function __toString()
    {
        $parts = [
            (string) $this->getHeaders(),
            (string) $this->getPayload(),
            Base64Encoder::encode($this->getSignature()),
        ];

        return implode('.', $parts);
    }

    /**
     * Creates Signed JWT from existing JWT
     *
     * @param JsonWebToken $jwt
     * @param string $signature
     * @return self
     */
    public static function createFromJsonWebtoken(JsonWebToken $jwt, string $signature)
    {
        return new self($jwt->headers, $jwt->payload, $signature);
    }
}
