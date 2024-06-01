<?php

namespace LittleApps\LittleJWT\Factories;

use Jose\Component\Core\Algorithm as AlgorithmContract;
use Jose\Component\Signature\Algorithm as JoseAlgorithms;
use LittleApps\LittleJWT\Exceptions\HashAlgorithmNotFoundException;
use LittleApps\LittleJWT\Exceptions\InvalidHashAlgorithmException;

class AlgorithmBuilder
{
    /**
     * Algorithm identifier-class mappings
     *
     * @var array<string, class-string<\Jose\Component\Core\Algorithm>>
     */
    protected static array $algorithmMappings = [
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
     * Gets algorithm class mapping
     *
     * @return array<string, class-string<\Jose\Component\Core\Algorithm>>
     */
    public static function getAlgorithmMappings(): array
    {
        return static::$algorithmMappings;
    }

    /**
     * Gets supported algorithm identifiers.
     *
     * @return list<string>
     */
    public static function getSupportedAlgorithmIdentifiers(): array
    {
        return array_keys(static::$algorithmMappings);
    }

    /**
     * Gets supported algorithm classes.
     *
     * @return list<class-string<\Jose\Component\Core\Algorithm>>
     */
    public static function getSupportedAlgorithmClasses(): array
    {
        return array_values(static::$algorithmMappings);
    }

    /**
     * Gets algorithm class from algorithm identifier.
     *
     * @param  string  $identifier  Algorithm identifier (ex: 'HS256')
     * @param  mixed  $default  Returned if identifier doesn't exist.
     * @return class-string<\Jose\Component\Core\Algorithm>|mixed
     */
    public static function getAlgorithmClass(string $identifier, $default = null)
    {
        return static::$algorithmMappings[$identifier] ?? $default;
    }

    /**
     * Builds Algorithm instance.
     *
     * @param  string  $identifier  Algorithm identifier (ex: 'HS256')
     */
    public static function build(string $identifier): AlgorithmContract
    {
        $alg = strtoupper($identifier);

        $class = static::getAlgorithmClass($alg);

        if (is_null($class)) {
            throw new InvalidHashAlgorithmException(sprintf('JSON Web Key algorithm "%s" is invalid.', $alg));
        }

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
}
