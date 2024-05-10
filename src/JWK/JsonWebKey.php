<?php

namespace LittleApps\LittleJWT\JWK;

use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm as JoseAlgorithms;
use LittleApps\LittleJWT\Exceptions\HashAlgorithmNotFoundException;
use LittleApps\LittleJWT\Exceptions\InvalidHashAlgorithmException;

class JsonWebKey extends JWK
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

    public function __construct(array $values)
    {
        parent::__construct($values);
    }

    /**
     * Gets hash algorithm instance based on 'alg' value for JWK.
     *
     * @return \Jose\Component\Core\Algorithm
     *
     * @throws HashAlgorithmNotFoundException Thrown if algorithm could not be determined.
     */
    public function algorithm()
    {
        $alg = $this->has('alg') ? strtoupper($this->get('alg')) : null;

        if (is_null($alg)) {
            throw new HashAlgorithmNotFoundException('Json Web Key doesn\'t have algorithm set.');
        }

        if (! isset($this->algorithms[$alg])) {
            throw new InvalidHashAlgorithmException(sprintf('Json Web Key algorithm "%s" is invalid.', $alg));
        }

        $class = $this->algorithms[$alg];

        if (! class_exists($class)) {
            throw new HashAlgorithmNotFoundException(
                sprintf(
                    'Class for Json Web Key algorithm "%s" is missing. '.
                    'Ensure the appropriate package is installed: https://web-token.spomky-labs.com/the-components/signed-tokens-jws/signature-algorithms',
                    $alg
                )
            );
        }

        return new $class();
    }

    /**
     * Creates JsonWebKey instance from base JWK instance.
     *
     * @return self
     */
    public static function createFromBase(JWK $jwk)
    {
        return new self($jwk->all());
    }
}
