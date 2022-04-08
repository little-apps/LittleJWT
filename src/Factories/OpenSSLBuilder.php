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
     * @param int $type One of PRIVATE_KEY_TYPES_* constants (default is PRIVATE_KEY_TYPES_RSA)
     * @param string $curveName Curve name (default is prime256v1)
     * @return \OpenSSLAsymmetricKey
     * @see https://www.php.net/manual/en/function.openssl-get-curve-names.php Possible curve names
     */
    public function generatePrivateKey($type = self::PRIVATE_KEY_TYPES_RSA, $curveName = 'prime256v1')
    {
        // Generate a new private (and public) key pair
        $pkey = openssl_pkey_new($this->getConfig() + [
            "private_key_type" => $type,
            "curve_name" => $curveName,
        ]);

        throw_if($pkey === false, OpenSSLException::class, openssl_error_string());

        return $pkey;
    }

    /**
     * Generates a CSR
     *
     * @param string $commonName
     * @param OpenSSLAsymmetricKey $privKey
     * @param string $digestAlg Algorithm to use
     * @return OpenSSLCertificateSigningRequest
     */
    public function generateCertificateSignRequest(string $commonName, OpenSSLAsymmetricKey $privKey, string $digestAlg = 'sha384')
    {
        $csr = openssl_csr_new(compact('commonName'), $privKey, $this->getConfig() + ['digest_alg' => $digestAlg]);

        throw_if($csr === false, OpenSSLException::class, openssl_error_string());

        return $csr;
    }

    /**
     * Generates a certificate
     *
     * @param OpenSSLCertificateSigningRequest $csr
     * @param OpenSSLAsymmetricKey $privKey
     * @param string $digestAlg Algorithm to use
     * @return OpenSSLCertificate
     */
    public function generateCertificate(OpenSSLCertificateSigningRequest $csr, OpenSSLAsymmetricKey $privKey, string $digestAlg = 'sha384')
    {
        $cert = openssl_csr_sign($csr, null, $privKey, 365, $this->getConfig() + ['digest_alg' => $digestAlg]);

        throw_if($cert === false, OpenSSLException::class, openssl_error_string());

        return $cert;
    }

    /**
     * Exports private key as string
     *
     * @param OpenSSLAsymmetricKey $privKey
     * @return string
     */
    public function exportPrivateKey(OpenSSLAsymmetricKey $privKey, string $passPhrase = null)
    {
        $exported = openssl_pkey_export($privKey, $output, $passPhrase, $this->getConfig());

        throw_if($exported === false, OpenSSLException::class, openssl_error_string());

        return $output;
    }

    /**
     * Exports certificate and private key to string in PKCS#12 format
     *
     * @param OpenSSLCertificate $cert
     * @param OpenSSLAsymmetricKey $privKey
     * @param string $passPhrase
     * @return string
     */
    public function exportPkcs12(OpenSSLCertificate $cert, OpenSSLAsymmetricKey $privKey, string $passPhrase = '')
    {
        $exported = openssl_pkcs12_export($cert, $output, $privKey, $passPhrase, $this->getConfig());

        throw_if($exported === false, OpenSSLException::class, openssl_error_string());

        return $output;
    }
}
