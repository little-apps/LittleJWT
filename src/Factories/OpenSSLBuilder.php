<?php

namespace LittleApps\LittleJWT\Factories;

use LittleApps\LittleJWT\Exceptions\OpenSSLException;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use OpenSSLCertificateSigningRequest;

class OpenSSLBuilder
{
    public const PRIVATE_KEY_TYPES_RSA = OPENSSL_KEYTYPE_RSA;
    public const PRIVATE_KEY_TYPES_DSA = OPENSSL_KEYTYPE_DSA;
    public const PRIVATE_KEY_TYPES_DH = OPENSSL_KEYTYPE_DH;
    public const PRIVATE_KEY_TYPES_EC = OPENSSL_KEYTYPE_EC;

    protected $config;

    /**
     * Initializes OpenSSLBuilder instance.
     *
     * @param array $config Default configuration options to pass to openssl functions.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Gets configuration options
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Generates a private key
     *
     * @param array $config Configuration options to pass to openssl_pkey_new(). (default: ["private_key_type" => static::PRIVATE_KEY_TYPES_RSA, "curve_name" => 'prime256v1'])
     * @return \OpenSSLAsymmetricKey
     * @see https://www.php.net/manual/en/function.openssl-get-curve-names.php Possible curve names
     */
    public function generatePrivateKey(array $config = [])
    {
        $defaultConfig = [
            "private_key_type" => static::PRIVATE_KEY_TYPES_RSA,
            "curve_name" => 'prime256v1',
        ];

        // Generate a new private (and public) key pair
        $pkey = openssl_pkey_new($config + $defaultConfig + $this->getConfig());

        throw_if($pkey === false, OpenSSLException::class, openssl_error_string());

        return $pkey;
    }

    /**
     * Generates a CSR
     *
     * @param string $commonName
     * @param OpenSSLAsymmetricKey|resource $privKey
     * @param array $config Configuration options to pass to openssl_csr_new(). (default: ['digest_alg' => 'sha384'])
     * @return OpenSSLCertificateSigningRequest|resource
     */
    public function generateCertificateSignRequest(string $commonName, $privKey, array $config = [])
    {
        $defaultConfig = [
            'digest_alg' => 'sha384'
        ];

        $csr = openssl_csr_new(compact('commonName'), $privKey, $config + $defaultConfig + $this->getConfig());

        throw_if($csr === false, OpenSSLException::class, openssl_error_string());

        return $csr;
    }

    /**
     * Generates a certificate
     *
     * @param OpenSSLCertificateSigningRequest|resource $csr
     * @param OpenSSLAsymmetricKey|resource $privKey
     * @param array $config Configuration options to pass to openssl_csr_sign(). (default: ['digest_alg' => 'sha384'])
     * @return OpenSSLCertificate|resource
     */
    public function generateCertificate($csr, $privKey, array $config = [])
    {
        $defaultConfig = [
            'digest_alg' => 'sha384'
        ];

        $cert = openssl_csr_sign($csr, null, $privKey, 365, $config + $defaultConfig + $this->getConfig());

        throw_if($cert === false, OpenSSLException::class, openssl_error_string());

        return $cert;
    }

    /**
     * Exports private key as string
     *
     * @param OpenSSLAsymmetricKey|resource $privKey
     * @param string $passPhrase
     * @param array $config Configuration options to pass to openssl_pkey_export(). (default: empty array)
     * @return string
     */
    public function exportPrivateKey($privKey, string $passPhrase = null, array $config = [])
    {
        $exported = openssl_pkey_export($privKey, $output, $passPhrase, $config + $this->getConfig());

        throw_if($exported === false, OpenSSLException::class, openssl_error_string());

        return $output;
    }

    /**
     * Exports certificate and private key to string in PKCS#12 format
     *
     * @param OpenSSLCertificate|resource $cert
     * @param OpenSSLAsymmetricKey|resource $privKey
     * @param string $passPhrase
     * @param array $config Configuration options to pass to openssl_pkcs12_export(). (default: empty array)
     * @return string
     */
    public function exportPkcs12($cert, $privKey, string $passPhrase = '', array $config = [])
    {
        $exported = openssl_pkcs12_export($cert, $output, $privKey, $passPhrase, $config + $this->getConfig());

        throw_if($exported === false, OpenSSLException::class, openssl_error_string());

        return $output;
    }
}
