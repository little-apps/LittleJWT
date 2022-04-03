<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;

use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;

use LittleApps\LittleJWT\Contracts\Keyable;
use LittleApps\LittleJWT\Exceptions\MissingKeyException;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class KeyBuilder implements Keyable
{
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

    protected $app;

    protected $config;

    protected $extra;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->extra = [
            'use' => 'sig',
        ];
    }

    /**
     * Builds a JWK to use to sign/verify JWTs
     *
     * @param array $config These configuration options override the options specified in the littlejwt.key config options. (default: empty array)
     * @return JWK
     */
    public function build(array $config = [])
    {
        $config = array_merge($this->config, $config);

        $keyType = isset($config['default']) ? $config['default'] : null;

        switch ($keyType) {
            case static::KEY_NONE: {
                return JWKFactory::createNoneKey();
            }

            case static::KEY_FILE: {
                return $this->buildFromFile($config[static::KEY_FILE]);
            }

            case static::KEY_SECRET: {
                return $this->buildFromSecret($config[static::KEY_SECRET]);
            }

            case static::KEY_RANDOM: {
                $size = $config[static::KEY_RANDOM]['size'] ?? 1024;

                return $this->generateRandomJwk($size);
            }

            default: {
                Log::warning('LittleJWT is reverting to use no key. This is NOT recommended.');

                return JWKFactory::createNoneKey();
            }
        }
    }

    /**
     * Generates a random JWK
     *
     * @param int $size # of bits for key size (must be multiple of 8)
     * @return JWK
     */
    public function generateRandomJwk($size = 1024)
    {
        return JWKFactory::createOctKey(
            $size, // Size in bits of the key. We recommend at least 128 bits.
            [
                'alg' => 'HS256', // This key must only be used with the HS256 algorithm
                'use' => 'sig',    // This key is used for signature/verification operations only
            ]
        );
    }

    /**
     * Builds JWK from secret phrase.
     *
     * @param array $config
     * @return JWK
     */
    public function buildFromSecret(array $config)
    {
        if (! isset($config['allow_unsecure']) || ! $config['allow_unsecure']) {
            if (! isset($config['phrase'])) {
                throw new MissingKeyException();
            } elseif ($config['phrase'] === '') {
                Log::warning('LittleJWT is using an empty secret phrase. This is NOT recommended.');
            }
        }

        $phrase = Base64Encoder::decode($config['phrase']);

        return JWKFactory::createFromSecret($phrase, $this->extra);
    }

    /**
     * Builds JWK from key file.
     *
     * @param array $config
     * @return JWK
     */
    public function buildFromFile($config)
    {
        if (! is_file($config['path'])) {
            throw new MissingKeyException();
        }

        switch ($config['type']) {
            case static::KEY_FILES_CRT: {
                    return JWKFactory::createFromCertificateFile($config['path'], $this->extra);
                }

            case static::KEY_FILES_P12: {
                    return JWKFactory::createFromPKCS12CertificateFile($config['path'], $config['secret'], $this->extra);
                }

            default: {
                    return JWKFactory::createFromKeyFile($config['path'], $config['secret'], $this->extra);
                }
        }
    }
}
