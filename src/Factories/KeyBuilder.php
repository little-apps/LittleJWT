<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Contracts\Foundation\Application;

use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Contracts\Keyable;
use LittleApps\LittleJWT\Exceptions\MissingKeyException;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class KeyBuilder implements Keyable
{
    const KEY_SECRET = 'secret';
    const KEY_FILE = 'file';
    const KEY_NONE = 'none';

    const KEY_FILES_PEM = 'pem';
    const KEY_FILES_P12 = 'p12';
    const KEY_FILES_CRT = 'crt';

    protected $app;

    protected $config;

    protected $extra;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->extra = [
            'use' => 'sig'
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

        if ($config['default'] === static::KEY_NONE)
            return JWKFactory::createNoneKey();

        return $config['default'] === static::KEY_FILE ? $this->buildFromFile($config[static::KEY_FILE]) : $this->buildFromSecret($config[static::KEY_SECRET]);
    }

    private function buildFromSecret($config)
    {
        if (empty($config['phrase']))
            throw new MissingKeyException();

        $phrase = Base64Encoder::decode($config['phrase']);

        return JWKFactory::createFromSecret($phrase, $this->extra);
    }

    private function buildFromFile($config)
    {
        if (!is_file($config['path']))
            throw new MissingKeyException();

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
