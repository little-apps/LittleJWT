<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Support\Str;

use LittleApps\LittleJWT\Build\Sign;

use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;
use LittleApps\LittleJWT\Utils\Base64Encoder;
use LittleApps\LittleJWT\Utils\JsonEncoder;

class JWTBuilder
{
    /**
     * Sign instance to use for signing JWTs later.
     *
     * @var Sign
     */
    protected $sign;

    /**
     * Initializes JWT Builder
     * @param Sign $sign Passed to JsonWebToken to be used by sign() method.
     */
    public function __construct(Sign $sign)
    {
        $this->sign = $sign;
    }

    /**
     * Builds a JWT instance from an existing JWT string.
     *
     * @param string $token
     * @return JsonWebToken
     * @throws CantParseJWTException Thrown if token cannot be parsed.
     */
    public function buildFromExisting(string $token)
    {
        $parts = Str::of($token)->explode('.');

        if ($parts->count() < 2 || $parts->count() > 3) {
            throw new CantParseJWTException();
        }

        return
            $this->buildFromParts(
                $this->decodeClaims($parts[0]),
                $this->decodeClaims($parts[1]),
                $parts->count() === 3 ? $parts[2] : null
            );
    }

    /**
     * Builds a JWT instance using the different parts.
     *
     * @param array $headers
     * @param array $payload
     * @param string|null $signature
     * @return JsonWebToken|SignedJsonWebToken Returns SignedJsonWebToken if signature is passed, otherwise JsonWebToken.
     * @throws CantParseJWTException Thrown if token cannot be parsed.
     */
    public function buildFromParts(array $headers, array $payload, ?string $signature = null)
    {
        if (is_null($signature)) {
            return new JsonWebToken($this->sign, $headers, $payload);
        } else {
            $signature = $this->decodeSignature($signature);

            return new SignedJsonWebToken($this->sign, $headers, $payload, $signature);
        }
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

    /**
     * Decodes signature (if needed) to raw bytes.
     *
     * @param string $signature
     * @return string
     */
    protected function decodeSignature($signature)
    {
        // Returns bytes if signature isn't already base64 encoded.
        $decoded = Base64Encoder::decode($signature);

        // If decoded, set signature to decoded.
        if ($decoded !== false) {
            $signature = $decoded;
        }

        return $signature;
    }
}
