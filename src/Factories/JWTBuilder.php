<?php

namespace LittleApps\LittleJWT\Factories;

use InvalidArgumentException;

use Illuminate\Support\Str;

use LittleApps\LittleJWT\Exceptions\CantParseJWTException;

use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\JWT\ClaimManager;

use LittleApps\LittleJWT\Utils\JsonEncoder;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class JWTBuilder {

    /**
     * Builds a JWT instance from an existing JWT string.
     *
     * @param string $token
     * @return JWT
     * @throws CantParseJWTException Thrown if token cannot be parsed.
     */
    public function buildFromExisting(string $token) {
        $parts = Str::of($token)->explode('.');

        if ($parts->count() !== 3)
            throw new CantParseJWTException();

        $headers = $this->buildClaimManagerFromPart($parts[0]);
        $payload = $this->buildClaimManagerFromPart($parts[1]);
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
    public function buildFromParts(ClaimManager $headers, ClaimManager $payload, $signature) {
        // Returns if signature isn't already base64 encoded.
        $decoded = Base64Encoder::decode($signature);

        // If decoded, set signature to decoded.
        if ($decoded !== false)
            $signature = $decoded;

        return new JWT($headers, $payload, $signature);
    }

    /**
     * Builds ClaimManager from a part.
     *
     * @param string $part
     * @return ClaimManager|null New ClaimManager instance or null if it cannot be created.
     * @throws CantParseJWTException Thrown if part cannot be decoded.
     */
    protected function buildClaimManagerFromPart($part) {
        $decoded = Base64Encoder::decode($part);

        if ($decoded === false)
            throw new CantParseJWTException();

        $array = JsonEncoder::decode($decoded);

        if (is_null($array))
            throw new CantParseJWTException();

        return new ClaimManager($array);
    }
}
