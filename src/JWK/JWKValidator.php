<?php

namespace LittleApps\LittleJWT\JWK;

use Closure;
use Jose\Component\Signature\Algorithm\ECDSA;
use Jose\Component\Signature\Algorithm\HMAC;
use Jose\Component\Signature\Algorithm\RSAPKCS1;
use Jose\Component\Signature\Algorithm\RSAPSS;
use LittleApps\LittleJWT\Exceptions\HashAlgorithmNotFoundException;
use LittleApps\LittleJWT\Exceptions\InvalidHashAlgorithmException;
use LittleApps\LittleJWT\Exceptions\InvalidJWKException;
use LittleApps\LittleJWT\Factories\AlgorithmBuilder;

class JWKValidator
{
    /**
     * Gets default callback for JWKValidator
     *
     * @var ?Closure
     */
    protected static ?Closure $defaults;

    /**
     * Fallback for when validation fails
     *
     * @var ?Closure(): static
     */
    protected ?Closure $fallback = null;

    /**
     * Initializes JWKValidator instance
     *
     */
    public function __construct()
    {
    }

    /**
     * Sets fallback
     *
     * @param callable(): static $fallback
     * @return void
     */
    public function withFallback(callable $fallback) {
        $this->fallback = $fallback;

        return $this;
    }

    public function withoutFallback() {
        $this->fallback = null;

        return $this;
    }

    /**
     * Validates the JSON Web Key
     *
     * @return JsonWebKey
     * @throws InvalidJWKException Thrown if JWK is invalid and fallback is not set.
     */
    public function __invoke(JsonWebKey $jsonWebKey)
    {
        try {
            $this->perform($jsonWebKey);

            return $jsonWebKey;
        } catch (InvalidJWKException $ex) {
            if (isset($this->fallback))
                return call_user_func($this->fallback);
            else
                throw $ex;
        }
    }

    /**
     * Performs the validation
     *
     * @return void
     * @throws InvalidJWKException Thrown if JWK is invalid.
     */
    protected function perform(JsonWebKey $jsonWebKey) {
        $values = $jsonWebKey->all();

        if (!isset($values['alg'])) {
            throw new InvalidJWKException("The 'alg' value is missing.");
        }

        if (!isset($values['kty'])) {
            throw new InvalidJWKException("The 'kty' value is missing.");
        }

        $this->validateAlgorithm($jsonWebKey);

        $algorithm = $this->getAlgorithm($jsonWebKey);

        if ($algorithm instanceof HMAC)
            $this->validateHmacKey($jsonWebKey);
        else if ($algorithm instanceof ECDSA)
            $this->validateEcdsaKey($jsonWebKey);
        else if ($algorithm instanceof RSAPSS || $algorithm instanceof RSAPKCS1)
            $this->validateRsaKey($jsonWebKey);
    }

    /**
     * Checks if algorithm is supported.
     *
     * @param JsonWebKey $jsonWebKey
     * @return void
     */
    protected function validateAlgorithm(JsonWebKey $jsonWebKey) {
        $alg = $jsonWebKey->get('alg');

        $class = AlgorithmBuilder::getAlgorithmClass($alg);

        if (is_null($class))
            throw new InvalidJWKException("JSON Web Key algorithm '{$alg}' is not supported.");

        try {
            $jsonWebKey->algorithm();
        } catch (InvalidHashAlgorithmException | HashAlgorithmNotFoundException $ex) {
            throw new InvalidJWKException($ex->getMessage());
        }
    }

    /**
     * Gets algorithm instance from JWK
     *
     * @param JsonWebKey $jsonWebKey
     * @return \Jose\Component\Core\Algorithm
     */
    protected function getAlgorithm(JsonWebKey $jsonWebKey) {
        try {
            return $jsonWebKey->algorithm();
        } catch (InvalidHashAlgorithmException | HashAlgorithmNotFoundException $ex) {
            throw new InvalidJWKException($ex->getMessage());
        }
    }

    /**
     * Validates an HMAC key.
     *
     * @return void
     */
    protected function validateHmacKey(JsonWebKey $jsonWebKey) {
        static $minKeyLengths = [
            'HS256' => 32,
            'HS384' => 48,
            'HS512' => 64,
        ];

        $alg = strtoupper($jsonWebKey->get('alg'));
        $key = $jsonWebKey->get('k') ?? '';

        if (mb_strlen($key, '8bit') < $minKeyLengths[$alg])
            throw new InvalidJWKException("The key is not long enough.");
    }

    /**
     * Validates an ECDSA key.
     *
     * @return void
     */
    protected function validateEcdsaKey(JsonWebKey $jsonWebKey) {
        $required = ['x', 'y', 'crv'];

        foreach ($required as $key) {
            if (!$jsonWebKey->has($key)) {
                throw new InvalidJWKException("The '{$key}' key is required in ECDSA JSON Web Keys.");
            }
        }
    }

    /**
     * Validates an RSA key.
     *
     * @return void
     */
    protected function validateRsaKey(JsonWebKey $jsonWebKey) {
        $required = ['n', 'e'];

        foreach ($required as $key) {
            if (!$jsonWebKey->has($key)) {
                throw new InvalidJWKException("The '{$key}' key is required in RSA JSON Web Keys.");
            }
        }
    }

    /**
     * Specifies callback for creating default JWKValidator
     *
     * @param callable $callback
     * @return void
     */
    public static function defaults(callable $callback): void {
        static::$defaults = $callback;
    }

    /**
     * Creates default JWKValidator instance.
     *
     * @return static
     */
    public static function default(): static {
        return isset(static::$defaults) ? call_user_func(static::$defaults) : new self;
    }
}
