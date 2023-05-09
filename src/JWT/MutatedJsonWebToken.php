<?php

namespace LittleApps\LittleJWT\JWT;

/**
 * Represents a mutated JSON Web Token (JWT).
 */
class MutatedJsonWebToken extends JsonWebToken
{
    /**
     * Original JWT
     *
     * @var JsonWebToken
     */
    protected $original;

    /**
     * Creates an instance that represents a JWT.
     *
     * @param JsonWebToken $original Original JWT
     * @param array $headers Headers
     * @param array $payload Payload
     */
    public function __construct(JsonWebToken $original, array $headers, array $payload)
    {
        parent::__construct($headers, $payload);

        $this->original = $original;
    }

    /**
     * Gets the original (serialized) JWT.
     * Use this for validating.
     *
     * @return JsonWebToken
     */
    public function getOriginal()
    {
        return $this->original;
    }
}
