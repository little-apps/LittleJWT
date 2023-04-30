<?php

namespace LittleApps\LittleJWT\Factories;

use InvalidArgumentException;
use Jose\Component\Core\Algorithm as AlgorithmContract;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm;

use LittleApps\LittleJWT\Exceptions\IncompatibleHashAlgorithmJWK;
use LittleApps\LittleJWT\JWT\ClaimManager;
use LittleApps\LittleJWT\JWT\JWT;

class JWTHasher
{
    /**
     * Algorithm to use for verifying and signing.
     *
     * @var AlgorithmContract
     */
    protected $algorithm;

    /**
     * Initializes JWTHasher instance
     *
     * @param AlgorithmContract $algorithm Algorithm to use for verifying and signing JWTs
     */
    public function __construct(AlgorithmContract $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Checks if JWT signature matches with JWK
     *
     * @param JWK $jwk JWK to use for verification
     * @param JWT $jwt JWT to test.
     * @return bool True if JWT signature is valid.
     */
    public function verify(JWK $jwk, JWT $jwt)
    {
        $expected = $jwt->getSignature();
        $input = $this->createInput($jwt->getHeaders(), $jwt->getPayload());

        return $this->algorithm->verify($jwk, $input, $expected);
    }

    /**
     * Generats a hash to be used as the signature for the JWT.
     *
     * @param JWK $jwk JWK to use to create signature.
     * @param JWT $jwt JWT to create signature for.
     * @return string
     * @throws IncompatibleHashAlgorithmJWK Thrown if the JWK is incompatible with the hashing algorithm.
     */
    public function hash(JWK $jwk, ClaimManager $headers, ClaimManager $payload)
    {
        $input = $this->createInput($headers, $payload);

        try {
            if ($this->algorithm instanceof Algorithm\MacAlgorithm) {
                return $this->algorithm->hash($jwk, $input);
            } else {
                return $this->algorithm->sign($jwk, $input);
            }
        } catch (InvalidArgumentException $e) {
            throw new IncompatibleHashAlgorithmJWK($e);
        }
    }

    /**
     * Creates the input that will be sent to the HMAC hashing function.
     *
     * @param ClaimManager $headers
     * @param ClaimManager $payload
     * @return string
     */
    protected function createInput(ClaimManager $headers, ClaimManager $payload)
    {
        return sprintf('%s.%s', (string) $headers, (string) $payload);
    }
}
