<?php

namespace LittleApps\LittleJWT\Factories;

use InvalidArgumentException;
use Jose\Component\Core\Algorithm as AlgorithmContract;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm;
use LittleApps\LittleJWT\Exceptions\IncompatibleHashAlgorithmJWK;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\ClaimManager;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;

class JWTHasher
{
    /**
     * Signs a JSON Web Token
     *
     * @return SignedJsonWebToken
     */
    public static function sign(JsonWebToken $jsonWebToken, JsonWebKey $jsonWebKey)
    {
        $algorithm = $jsonWebKey->algorithm();
        $signature = JWTHasher::hash($algorithm, $jsonWebKey, $jsonWebToken->getHeaders(), $jsonWebToken->getPayload());

        return SignedJsonWebToken::instance($jsonWebToken, $signature);
    }

    /**
     * Checks if JWT signature matches with JWK
     *
     * @param  AlgorithmContract  $algorithm  Algorithm to use for verifying JWT
     * @param  JsonWebKey  $jwk  JWK to use for verification
     * @param  JsonWebToken  $jwt  JWT to test.
     * @return bool True if JWT signature is valid.
     */
    public static function verify(AlgorithmContract $algorithm, JsonWebKey $jwk, JsonWebToken $jwt)
    {
        if (! ($jwt instanceof SignedJsonWebToken)) {
            return false;
        }

        $expected = $jwt->getSignature();
        $input = static::createInput($jwt->getHeaders(), $jwt->getPayload());

        return $algorithm->verify($jwk, $input, $expected);
    }

    /**
     * Generats a hash to be used as the signature for the JWT.
     *
     * @param  AlgorithmContract  $algorithm  Algorithm to use for generating signature.
     * @param  JsonWebKey  $jwk  JWK to use to create signature.
     * @param  ClaimManager  $headers  Header claims used to create signature.
     * @param  ClaimManager  $payload  Payload claims used to create signature.
     * @return string
     *
     * @throws IncompatibleHashAlgorithmJWK Thrown if the JWK is incompatible with the hashing algorithm.
     */
    public static function hash(AlgorithmContract $algorithm, JsonWebKey $jwk, ClaimManager $headers, ClaimManager $payload)
    {
        $input = static::createInput($headers, $payload);

        try {
            if ($algorithm instanceof Algorithm\MacAlgorithm) {
                return $algorithm->hash($jwk, $input);
            } else {
                return $algorithm->sign($jwk, $input);
            }
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Wrong key type.') {
                throw new IncompatibleHashAlgorithmJWK($e);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Creates the input that will be sent to the HMAC hashing function.
     *
     * @return string
     */
    protected static function createInput(ClaimManager $headers, ClaimManager $payload)
    {
        return sprintf('%s.%s', (string) $headers, (string) $payload);
    }
}
