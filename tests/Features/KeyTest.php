<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Exceptions\HashAlgorithmNotFoundException;
use LittleApps\LittleJWT\Exceptions\InvalidHashAlgorithmException;
use LittleApps\LittleJWT\Exceptions\MissingKeyException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\Factories\OpenSSLBuilder;
use LittleApps\LittleJWT\Testing\TestValidator;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class KeyTest extends TestCase
{
    use InteractsWithLittleJWT;
    use WithFaker;

    /**
     * Tests the JWT is created and validated using a JWK secret.
     *
     * @return void
     */
    public function test_create_validate_jwk_secret()
    {
        $phrase = Base64Encoder::encode($this->faker->sha1());
        $jwk = KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            'secret' => [
                'phrase' => $phrase,
            ],
        ]);

        LittleJWT::fake($jwk);

        $passes = $this->createValidateWithJwk($jwk);

        $this->assertTrue($passes);
    }

    /**
     * Tests a JWK secret with a missing phrase is attempted.
     *
     * @return void
     */
    public function test_create_validate_jwk_secret_missing_phrase_attempted()
    {
        $this->expectException(MissingKeyException::class);

        KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            'secret' => [],
        ]);
    }

    /**
     * Tests a JWK secret with an empty phrase is attempted.
     *
     * @return void
     */
    public function test_create_validate_jwk_secret_empty_phrase_attempted()
    {
        $spy = Log::spy();

        KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            'secret' => ['allow_unsecure' => false, 'phrase' => ''],
        ]);

        $spy->shouldHaveReceived('warning', ['LittleJWT is using an empty secret phrase. This is NOT recommended.']);
    }

    /**
     * Tests a JWK secret with an empty phrase is created.
     *
     * @return void
     */
    public function test_create_validate_jwk_secret_empty_phrase_created()
    {
        $spy = Log::spy();

        $jwk = KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            'secret' => ['allow_unsecure' => true, 'phrase' => ''],
        ]);

        $this->assertInstanceOf(JWK::class, $jwk);

        $spy->shouldNotHaveReceived('warning', ['LittleJWT is using an empty secret phrase. This is NOT recommended.']);
    }

    /**
     * Tests the JWK is properly base64url encoded.
     *
     * @return void
     */
    public function test_jwk_base64url_encoded()
    {
        $dangerous = base64_decode('+/n7+P37/fj6+fv8+Pr8+fr9/Pr7+/v7/Pj9+fz9+Pn6+vr6+/r4/Pv7+Pr8+f38+Pz4+Pr9/fv5/fr8+/z4+vz8+fv7+fz9+Pz7+/38/Pj7/Pj8+fj9/fz6/Pr8/Pn5+v37+g==');

        $phrase = Base64Encoder::encode($dangerous);
        $jwk = KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            'secret' => [
                'phrase' => $phrase,
            ],
        ]);

        LittleJWT::fake($jwk);

        $token = (string) LittleJWT::create(function (Builder $builder) {
            $builder->withoutDefaults()->foo('bar');
        }, false)->sign();

        $this->assertTrue(strpos($token, '-') !== false || strpos($token, '_') !== false);
        $this->assertTrue(strpos($token, '+') === false || strpos($token, '/') === false);
    }

    /**
     * Tests that base64 is properly encoded for the HTTP query
     *
     * @return void
     */
    public function test_base64url_encoded_query()
    {
        // No need to regenerate binary data for every test run
        $phrase = Base64Encoder::encode(base64_decode('+fv5/Pr8+fj8/fv4+v36/fn8/fv7+fj9+/34+Pn7/Po='));
        $jwk = KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            'secret' => [
                'phrase' => $phrase,
            ],
        ]);

        LittleJWT::fake($jwk);

        $token = (string) LittleJWT::create();
        $uuid = $this->faker->uuid();

        // Creates query with sprintf, because http_build_query escapes special characters (like + and =)
        $response = $this->getJson(sprintf('/api/io?token=%s&uuid=%s', $token, $uuid));

        // Tests the token (and UUID) are parsed correctly
        $response->assertJson(['body' => compact('token', 'uuid')]);
    }

    /**
     * Tests the JWT is created and validated using a private key file.
     *
     * @return void
     */
    public function test_create_validate_jwk_prv_key_file()
    {
        Storage::fake();

        $openssl = $this->app[OpenSSLBuilder::class];

        Storage::put('jwk.key', $openssl->exportPrivateKey($openssl->generatePrivateKey()));

        $config = [
            'type' => KeyBuilder::KEY_FILES_PEM,
            'path' => Storage::path('jwk.key'),
            'secret' => '',
        ];

        $jwk = KeyBuilder::buildFromFile($config, ['alg' => 'RS256']);

        $passes = $this->createValidateWithJwk($jwk);

        $this->assertTrue($passes);
    }

    /**
     * Tests the JWT is created and validated using a private key.
     *
     * @return void
     */
    public function test_create_validate_jwk_prv()
    {
        Storage::fake();

        $openssl = $this->app[OpenSSLBuilder::class];
        $privKey = $openssl->exportPrivateKey($openssl->generatePrivateKey());

        $jwk = KeyBuilder::createFromKey($privKey, '', ['alg' => 'RS256']);

        $passes = $this->createValidateWithJwk($jwk);

        $this->assertTrue($passes);
    }

    /**
     * Tests the JWT is created and validated using a PKCS#12 certificate.
     *
     * @return void
     */
    public function test_create_validate_jwk_p12()
    {
        Storage::fake();

        $openssl = $this->app[OpenSSLBuilder::class];

        $privKey = $openssl->generatePrivateKey();
        $csr = $openssl->generateCertificateSignRequest($this->faker->domainName(), $privKey);
        $crt = $openssl->generateCertificate($csr, $privKey);

        Storage::put('jwk.p12', $openssl->exportPkcs12($crt, $privKey));

        $jwk = KeyBuilder::wrap(JWKFactory::createFromPKCS12CertificateFile(Storage::path('jwk.p12'), '', ['alg' => 'RS256']));

        $this->assertTrue($this->createValidateWithJwk($jwk));
    }

    /**
     * Tests the InvalidHashAlgorithmException is thrown when an invalid algorithm is set.
     *
     * @return void
     */
    public function test_invalid_hash_algorithm_thrown()
    {
        $jwk = KeyBuilder::generateRandomJwk(1024, ['alg' => 'FOO']);

        $this->expectException(InvalidHashAlgorithmException::class);

        LittleJWT::fake($jwk);

        LittleJWT::create()->sign();
    }

    /**
     * Tests the InvalidHashAlgorithmException is thrown when no 'alg' is set.
     *
     * @return void
     */
    public function test_no_alg_throws_exception()
    {
        $jwk = KeyBuilder::wrap(JWKFactory::createOctKey(1024));

        $this->expectException(HashAlgorithmNotFoundException::class);

        LittleJWT::fake($jwk);

        LittleJWT::create()->sign();
    }

    /**
     * Tests that a unverified JWT with the none algorithm is not allowed.
     * See https://auth0.com/blog/critical-vulnerabilities-in-json-web-token-libraries/ for more information.
     *
     * @return void
     */
    public function test_none_algorithm_vulnerability()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        $headers = $jwt->getHeaders()->toArray();
        $payload = $jwt->getPayload()->toArray();

        $headers['alg'] = 'none';

        $bad = LittleJWT::createJWTBuilder()->buildFromParts($headers, $payload, $jwt->getSignature());

        $valid = LittleJWT::validate($bad, function (TestValidator $validator) {
            $validator
                ->assertFails()
                ->assertInvalidSignature();
        });

        $this->assertFalse($valid->passes());
    }

    /**
     * Tests there's no 'alg' set in the config file.
     *
     * @return void
     */
    public function test_config_file_missing_alg()
    {
        $config = config('littlejwt.key', []);

        unset($config['alg']);

        $jwk = KeyBuilder::buildFromConfig($config);

        $this->assertNotNull($jwk);

        LittleJWT::fake($jwk);

        $signed = LittleJWT::create()->sign();

        $this->assertNotNull($signed);
    }

    /**
     * Creates and validates a JWT with the same JWK
     *
     * @param  JWK  $jwk  JWK to use to create and validate token.
     * @return bool True if JWT is valid.
     */
    protected function createValidateWithJwk(JWK $jwk)
    {
        LittleJWT::fake(KeyBuilder::wrap($jwk));

        $canary = $this->faker->uuid();

        $jwt = LittleJWT::create(function (Builder $builder) use ($canary) {
            $builder->can($canary);
        })->sign();

        return LittleJWT::validate($jwt, function (TestValidator $validator) use ($canary) {
            $validator
                ->assertPasses()
                ->assertClaimMatches('can', $canary);
        })->passes();
    }
}
