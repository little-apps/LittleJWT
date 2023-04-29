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
    protected $claimManagerBuilder;

    public function __construct(ClaimManagerBuilder $claimManagerBuilder)
    {
        $this->claimManagerBuilder = $claimManagerBuilder;
    }

    /**
     * Builds a JWT instance from an existing JWT string.
     *
     * @param string $token
     * @return JWT
     * @throws CantParseJWTException Thrown if token cannot be parsed.
     */
    public function buildFromExisting(string $token, array $mutators)
    {
        $parts = Str::of($token)->explode('.');

        if ($parts->count() !== 3) {
            throw new CantParseJWTException();
        }

        // Create claim managers for header and payload.
        $headers = $this->claimManagerBuilder->buildClaimManagerForHeader(
            $this->decodeClaims($parts[0]),
            $mutators['header']
        )->unserialized();

        $payload = $this->claimManagerBuilder->buildClaimManagerForPayload(
            $this->decodeClaims($parts[1]),
            $mutators['payload']
        )->unserialized();

        $signature = Base64Encoder::decode($parts[2]);

        return $this->buildFromParts($headers, $payload, $signature);
    }

    /**
     * Builds a JWT instance using the different parts.
     *
     * @param ClaimManager $headers
     * @param ClaimManager $payload
     * @param string $signature
     * @return JWT
     * @throws CantParseJWTException Thrown if token cannot be parsed.
     */
    public function buildFromParts(ClaimManager $headers, ClaimManager $payload, $signature)
    {
        // Returns bytes if signature isn't already base64 encoded.
        $decoded = Base64Encoder::decode($signature);

        // If decoded, set signature to decoded.
        if ($decoded !== false) {
            $signature = $decoded;
        }

        return new JWT($headers, $payload, $signature);
    }

    /**
     * Decodes JWT claims part into an array.
     *
     * @param string $claims
     * @return array Array of claims
     * @throws CantParseJWTException Thrown if part cannot be decoded.
     */
    protected function decodeClaims(string $claims)
    {
        $decoded = Base64Encoder::decode($claims);

        if ($decoded === false) {
            throw new CantParseJWTException();
        }

        $array = JsonEncoder::decode($decoded);

        if (! is_array($array)) {
            throw new CantParseJWTException();
        }

        return $array;
    }
}
