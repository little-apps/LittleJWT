<?php

namespace LittleApps\LittleJWT\JWK;

use Jose\Component\Core\Algorithm;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm as JoseAlgorithms;
use Jose\Component\Signature\Algorithm\ECDSA;
use Jose\Component\Signature\Algorithm\HMAC;
use Jose\Component\Signature\Algorithm\RSAPKCS1;
use Jose\Component\Signature\Algorithm\RSAPSS;
use LittleApps\LittleJWT\Exceptions\HashAlgorithmNotFoundException;
use LittleApps\LittleJWT\Exceptions\InvalidHashAlgorithmException;
use LittleApps\LittleJWT\Exceptions\InvalidJWKException;

class JWKValidator
{
    protected $algorithms = [
        'HS256' => JoseAlgorithms\HS256::class,
        'HS384' => JoseAlgorithms\HS384::class,
        'HS512' => JoseAlgorithms\HS512::class,

        'ES256' => JoseAlgorithms\ES256::class,
        'ES384' => JoseAlgorithms\ES384::class,
        'ES512' => JoseAlgorithms\ES512::class,

        'RS256' => JoseAlgorithms\RS256::class,
        'RS384' => JoseAlgorithms\RS384::class,
        'RS512' => JoseAlgorithms\RS512::class,

        'PS256' => JoseAlgorithms\PS256::class,
        'PS384' => JoseAlgorithms\PS384::class,
        'PS512' => JoseAlgorithms\PS512::class,

        'EDDSA' => JoseAlgorithms\EdDSA::class,

        'NONE' => JoseAlgorithms\None::class,
    ];

    /**
     * Initializes JWKValidator instance
     *
     * @param JsonWebKey $jsonWebKey
     */
    public function __construct(
        protected readonly JsonWebKey $jsonWebKey
    )
    {
    }

    /**
     * Validates the JSON Web Key
     *
     * @return void
     * @throws InvalidJWKException Thrown if JWK is invalid.
     */
    public function __invoke() {
        $values = $this->jsonWebKey->all();

        if (!isset($values['alg'])) {
            throw new InvalidJWKException("The 'alg' value is missing.");
        }

        if (!isset($values['kty'])) {
            throw new InvalidJWKException("The 'kty' value is missing.");
        }

        $alg = strtoupper($values['alg']);

        $algorithm = $this->jsonWebKey->algorithm();

        if (!$this->isAlgorithmSupported($algorithm)) {
            throw new InvalidJWKException("JSON Web Key algorithm '{$alg}' is not supported.");
        }

        if ($algorithm instanceof HMAC)
            $this->validateHmacKey();
        else if ($algorithm instanceof ECDSA)
            $this->validateEcdsaKey();
        else if ($algorithm instanceof RSAPSS || $algorithm instanceof RSAPKCS1)
            $this->validateRsaKey();
    }

    /**
     * Checks if algorithm is supported.
     *
     * @param Algorithm $algorithm
     * @return boolean
     */
    protected function isAlgorithmSupported(Algorithm $algorithm): bool {
        $algorithms = array_flip($this->algorithms);

        return isset($algorithms[get_class($algorithm)]);
    }

    /**
     * Validates an HMAC key.
     *
     * @return void
     */
    protected function validateHmacKey() {
        static $minKeyLengths = [
            'HS256' => 32,
            'HS384' => 48,
            'HS512' => 64,
        ];

        $alg = strtoupper($this->jsonWebKey->get('alg'));
        $key = $this->jsonWebKey->get('k') ?? '';

        if (mb_strlen($key, '8bit') < $minKeyLengths[$alg])
            throw new InvalidJWKException("The key is not long enough.");
    }

    /**
     * Validates an ECDSA key.
     *
     * @return void
     */
    protected function validateEcdsaKey() {
        $required = ['x', 'y', 'crv'];

        foreach ($required as $key) {
            if (!$this->jsonWebKey->has($key)) {
                throw new InvalidJWKException("The '{$key}' key is required in ECDSA JSON Web Keys.");
            }
        }
    }

    /**
     * Validates an RSA key.
     *
     * @return void
     */
    protected function validateRsaKey() {
        $required = ['n', 'e'];

        foreach ($required as $key) {
            if (!$this->jsonWebKey->has($key)) {
                throw new InvalidJWKException("The '{$key}' key is required in RSA JSON Web Keys.");
            }
        }
    }

    /**
     * Runs JWK validation
     *
     * @param JsonWebKey $jsonWebKey
     * @return void
     * @throws InvalidJWKException
     */
    public static function validate(JsonWebKey $jsonWebKey) {
        (new self($jsonWebKey))->__invoke();
    }
}
