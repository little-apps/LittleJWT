<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Traits\ForwardsCalls;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use LittleApps\LittleJWT\Exceptions\InvalidJWKException;
use LittleApps\LittleJWT\Exceptions\MissingKeyException;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class KeyBuilder
{
    use ForwardsCalls;

    /**
     * Used to specify a secret phrase is used to create JWK.
     */
    public const KEY_SECRET = 'secret';

    /**
     * Used to specify a key file is used to create JWK. The KEY_FILES_* constants are the different types of acceptable key files.
     */
    public const KEY_FILE = 'file';

    /**
     * Used to specify a random JWK is used each time.
     */
    public const KEY_RANDOM = 'random';

    /**
     * Used to specify no JWK is used (not recommended).
     */
    public const KEY_NONE = 'none';

    public const KEY_FILES_PEM = 'pem';

    public const KEY_FILES_P12 = 'p12';

    public const KEY_FILES_CRT = 'crt';

    /**
     * The default hashing algorithm to use when no algorithm is set.
     */
    public const DEFAULT_ALGORITHM = 'HS256';

    /**
     * Builds JSON Web Key from configuration.
     *
     * @return JsonWebKey
     */
    public static function buildFromConfig(array $config)
    {
        $keyType = isset($config['default']) ? $config['default'] : 'unknown';

        $options = match ($keyType) {
            static::KEY_NONE => [],
            static::KEY_FILE => $config[static::KEY_FILE],
            static::KEY_SECRET => $config[static::KEY_SECRET],
            static::KEY_RANDOM => ['size' => $config[static::KEY_RANDOM]['size'] ?? 1024],
            default => []
        };

        $extra = [
            'use' => 'sig',
            'alg' => $config['alg'] ?? static::DEFAULT_ALGORITHM,
        ];

        return static::build($keyType, $options, $extra);
    }

    /**
     * Builds a JWK to use to sign/verify JWTs
     *
     * @param  string  $keyType  Key type (one of KEY_* constants).
     * @param  array  $options  Options to build key type.
     * @param  array  $extra  Any extra values to include in JWK.
     * @return JsonWebKey
     *
     * @throws InvalidJWKException Thrown if JWK is invalid.
     */
    public static function build(string $keyType, array $options, array $extra)
    {
        switch ($keyType) {
            case static::KEY_NONE:
                $jwk = static::createNoneJwk($extra);

                break;

            case static::KEY_FILE:
                $jwk = static::buildFromFile($options, $extra);

                break;

            case static::KEY_SECRET:
                $jwk = static::buildFromSecret($options, $extra);

                break;

            case static::KEY_RANDOM:
                $size = $options['size'] ?? 1024;

                $jwk = static::generateRandomJwk($size, $extra);

                break;

            default:
                Log::warning('LittleJWT is reverting to use no key. This is NOT recommended.');

                $jwk = static::createNoneJwk($extra);

                break;
        }

        return $jwk;
    }

    /**
     * Creates a none key.
     *
     * @return JsonWebKey
     */
    public static function createNoneJwk(array $extra = [])
    {
        return static::wrap(JWKFactory::createNoneKey($extra));
    }

    /**
     * Generates a random JWK
     *
     * @param  int  $size  # of bits for key size (must be multiple of 8)
     * @return JsonWebKey
     */
    public static function generateRandomJwk($size = 1024, array $extra = [])
    {
        return static::wrap(JWKFactory::createOctKey(
            $size, // Size in bits of the key. We recommend at least 128 bits.
            $extra
        ));
    }

    /**
     * Builds JWK from secret phrase.
     *
     * @return JsonWebKey
     */
    public static function buildFromSecret(array $config, array $extra = [])
    {
        if (! isset($config['allow_unsecure']) || ! $config['allow_unsecure']) {
            if (! isset($config['phrase'])) {
                throw new MissingKeyException;
            } elseif ($config['phrase'] === '') {
                Log::warning('LittleJWT is using an empty secret phrase. This is NOT recommended.');
            }
        }

        $phrase = Base64Encoder::decode($config['phrase']);

        return static::wrap(JWKFactory::createFromSecret($phrase, $extra));
    }

    /**
     * Builds JWK from key file.
     *
     * @return JsonWebKey
     */
    public static function buildFromFile(array $config, array $extra = [])
    {
        if (! is_file($config['path'])) {
            throw new MissingKeyException;
        }

        switch ($config['type']) {
            case static::KEY_FILES_CRT:
                $jwk = JWKFactory::createFromCertificateFile($config['path'], $extra);

                break;

            case static::KEY_FILES_P12:
                $jwk = JWKFactory::createFromPKCS12CertificateFile($config['path'], $config['secret'], $extra);

                break;

            default:
                $jwk = JWKFactory::createFromKeyFile($config['path'], $config['secret'], $extra);

                break;
        }

        return static::wrap($jwk);
    }

    /**
     * Creates JSON Web Key from existing JWK
     *
     * @return JsonWebKey
     */
    public static function wrap(JWK $jwk)
    {
        return $jwk instanceof JsonWebKey ? $jwk : new JsonWebKey($jwk->all());
    }

    /**
     * Forwards calls to static methods in JWKFactory
     *
     * @param  string  $name  Method name
     * @param  array  $params  Method parameters
     * @return mixed
     *
     * @throws \BadMethodCallException Thrown if method doesn't exist in JWKFactory
     */
    public static function __callStatic($name, $params)
    {
        if (method_exists(JWKFactory::class, $name)) {
            return call_user_func_array([JWKFactory::class, $name], $params);
        }

        static::throwBadMethodCallException($name);
    }
}
