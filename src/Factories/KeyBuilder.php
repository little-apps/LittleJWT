<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Traits\ForwardsCalls;

use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;

use LittleApps\LittleJWT\Contracts\Keyable;
use LittleApps\LittleJWT\Exceptions\MissingKeyException;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class KeyBuilder implements Keyable
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
     * Application container.
     *
     * @var Application
     */
    protected $app;

    /**
     * Configuration options for building keys.
     *
     * @var array
     */
    protected $config;

    /**
     * Extra configuration options to pass to JWKFactory.
     *
     * @var array
     */
    protected $extra;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;

        $this->extra = ['use' => 'sig', 'alg' => $config['alg']];
    }

    /**
     * Builds a JWK to use to sign/verify JWTs
     *
     * @param array $config These configuration options override the options specified in the littlejwt.key config options. (default: empty array)
     * @return JsonWebKey
     */
    public function build(array $config = [])
    {
        $config = array_merge($this->config, $config);

        $keyType = isset($config['default']) ? $config['default'] : null;

        switch ($keyType) {
            case static::KEY_NONE: {
                return $this->createNoneJwk($this->extra);
            }

            case static::KEY_FILE: {
                return $this->buildFromFile($config[static::KEY_FILE], $this->extra);
            }

            case static::KEY_SECRET: {
                return $this->buildFromSecret($config[static::KEY_SECRET], $this->extra);
            }

            case static::KEY_RANDOM: {
                $size = $config[static::KEY_RANDOM]['size'] ?? 1024;

                return $this->generateRandomJwk($size, $this->extra);
            }

            default: {
                Log::warning('LittleJWT is reverting to use no key. This is NOT recommended.');

                return $this->createNoneJwk($this->extra);
            }
        }
    }

    /**
     * Creates a none key.
     *
     * @return JsonWebKey
     */
    public function createNoneJwk(array $extra = []) {
        return $this->createJwkFromBase(JWKFactory::createNoneKey(array_merge($this->extra, $extra)));
    }

    /**
     * Generates a random JWK
     *
     * @param int $size # of bits for key size (must be multiple of 8)
     * @return JsonWebKey
     */
    public function generateRandomJwk($size = 1024, array $extra = [])
    {
        return $this->createJwkFromBase(JWKFactory::createOctKey(
            $size, // Size in bits of the key. We recommend at least 128 bits.
            array_merge($this->extra, $extra)
        ));
    }

    /**
     * Builds JWK from secret phrase.
     *
     * @param array $config
     * @return JsonWebKey
     */
    public function buildFromSecret(array $config, array $extra = [])
    {
        if (! isset($config['allow_unsecure']) || ! $config['allow_unsecure']) {
            if (! isset($config['phrase'])) {
                throw new MissingKeyException();
            } elseif ($config['phrase'] === '') {
                Log::warning('LittleJWT is using an empty secret phrase. This is NOT recommended.');
            }
        }

        $phrase = Base64Encoder::decode($config['phrase']);

        return $this->createJwkFromBase(JWKFactory::createFromSecret($phrase, array_merge($this->extra, $extra)));
    }

    /**
     * Builds JWK from key file.
     *
     * @param array $config
     * @return JsonWebKey
     */
    public function buildFromFile(array $config, array $extra = [])
    {
        if (! is_file($config['path'])) {
            throw new MissingKeyException();
        }

        $extra = array_merge($this->extra, $extra);

        switch ($config['type']) {
            case static::KEY_FILES_CRT: {
                $jwk = JWKFactory::createFromCertificateFile($config['path'], $extra);
            }

            case static::KEY_FILES_P12: {
                $jwk = JWKFactory::createFromPKCS12CertificateFile($config['path'], $config['secret'], $extra);
            }

            default: {
                $jwk = JWKFactory::createFromKeyFile($config['path'], $config['secret'], $extra);
            }
        }

        return $this->createJwkFromBase($jwk);
    }

    /**
     * Creates JSON Web Key from existing JWK
     *
     * @param JWK $jwk
     * @return JsonWebKey
     */
    public function createJwkFromBase(JWK $jwk) {
        return JsonWebKey::createFromBase($jwk);
    }

    /**
     * Forwards calls to static methods in JWKFactory
     *
     * @param string $name Method name
     * @param array $params Method parameters
     * @return mixed
     * @throws BadMethodCallException Thrown if method doesn't exist in JWKFactory
     */
    public function __call($name, $params)
    {
        if (method_exists(JWKFactory::class, $name)) {
            return call_user_func_array([JWKFactory::class, $name], $params);
        }

        static::throwBadMethodCallException($name);
    }
}
