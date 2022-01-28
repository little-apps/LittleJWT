<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Support\Str;

use LittleApps\LittleJWT\Exceptions\CantParseJWTException;

use LittleApps\LittleJWT\JWT\ClaimManager;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Utils\Base64Encoder;
use LittleApps\LittleJWT\Utils\JsonEncoder;

class JWTBuilder
{
    /**
     * Builds a JWT instance from an existing JWT string.
     *
     * @param string $token
     * @param array $payloadMutators Mutators to use for payload claims.
     * @param array $headerMutators Mutators to use for header claims.
     * @return JWT
     * @throws CantParseJWTException Thrown if token cannot be parsed.
     */
    public function buildFromExisting(string $token, array $payloadMutators = [], array $headerMutators = [])
    {
        $parts = Str::of($token)->explode('.');

        if ($parts->count() !== 3) {
            throw new CantParseJWTException();
        }

        $headers = $this->buildClaimManagerFromPart($parts[0], $headerMutators);
        $payload = $this->buildClaimManagerFromPart($parts[1], $payloadMutators);
        $signature = Base64Encoder::decode($parts[2]);

        return new JWT($headers, $payload, $signature);
    }

    /**
     * Builds a JWT instance using the different parts.
     *
     * @param ClaimManager $headers
     * @param ClaimManager $payload
     * @param string $signature
     * @return JWT
     */
    public function buildFromParts(ClaimManager $headers, ClaimManager $payload, $signature)
    {
        // Returns if signature isn't already base64 encoded.
        $decoded = Base64Encoder::decode($signature);

        // If decoded, set signature to decoded.
        if ($decoded !== false) {
            $signature = $decoded;
        }

        return new JWT($headers, $payload, $signature);
    }

    /**
     * Builds ClaimManager from a part.
     *
     * @param string $part
     * @param array $mutators
     * @return ClaimManager|null New ClaimManager instance or null if it cannot be created.
     * @throws CantParseJWTException Thrown if part cannot be decoded.
     */
    protected function buildClaimManagerFromPart(string $part, array $mutators)
    {
        $decoded = Base64Encoder::decode($part);

        if ($decoded === false) {
            throw new CantParseJWTException();
        }

        $array = JsonEncoder::decode($decoded);

        if (is_null($array)) {
            throw new CantParseJWTException();
        }

        return new ClaimManager($array, $mutators);
    }
}
