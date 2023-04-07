<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;

use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Keyable;
use LittleApps\LittleJWT\Exceptions\InvalidHashAlgorithmException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\Factories\OpenSSLBuilder;
use LittleApps\LittleJWT\Testing\TestValidator;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

use LittleApps\LittleJWT\Utils\Base64Encoder;

class KeyTest extends TestCase
{
    use WithFaker;
    use InteractsWithLittleJWT;

    /**
     * Tests that a JWK secret phrase is generated using the littlejwt:phrase command.
     *
     * @return void
     */
    public function test_secret_phrase_generated()
    {
        $this
            ->artisan('littlejwt:phrase -d')
                ->expectsOutput('Generated secret key:')
                ->assertExitCode(0);
    }

    /**
     * Tests that a PKCS12 key is outputted.
     *
     * @return void
     */
    public function test_p12_outputted()
    {
        $this
            ->artisan('littlejwt:p12')
                ->assertExitCode(0);
    }

    /**
     * Tests that a .p12 file is generated using the littlejwt:p12 command.
     *
     * @return void
     */
    public function test_p12_file_generated()
    {
        Storage::fake();

        $path = str_replace('\\', '//', Storage::path('jwk.p12'));

        Storage::assertMissing('jwk.p12');

        $this
            ->artisan(sprintf('littlejwt:p12 "%s" --display', $path))
                ->expectsOutput('Generated environment variables:')
                ->assertExitCode(0);

        Storage::assertExists('jwk.p12');
    }

    /**
     * Tests that a .p12 file is not overwritten using the littlejwt:p12 command.
     *
     * @return void
     */
    public function test_p12_file_not_overwritten()
    {
        Storage::fake();

        $existing = $this->faker->text;
        Storage::put('jwk.p12', $existing);

        $path = str_replace('\\', '//', Storage::path('jwk.p12'));

        Storage::assertExists('jwk.p12');

        $this
            ->artisan(sprintf('littlejwt:p12 "%s" --display', $path))
                ->assertExitCode(1);

        $this->assertStringEqualsFile($path, $existing);
    }

    /**
     * Tests that a .p12 file is not overwritten using the littlejwt:p12 command.
     *
     * @return void
     */
    public function test_p12_file_overwritten()
    {
        Storage::fake();

        $existing = $this->faker->text;

        Storage::put('jwk.p12', $existing);

        $path = str_replace('\\', '//', Storage::path('jwk.p12'));

        Storage::assertExists('jwk.p12');

        $this
            ->artisan(sprintf('littlejwt:p12 "%s" --display --force', $path))
                ->assertExitCode(0)
                ->run();

        $this->assertStringNotEqualsFile($path, $existing);
    }

    /**
     * Tests that a PEM key is outputted.
     *
     * @return void
     */
    public function test_pem_outputted()
    {
        $this
            ->artisan('littlejwt:pem')
                ->assertExitCode(0);
    }

    /**
     * Tests that a .pem file is generated using the littlejwt:pem command.
     *
     * @return void
     */
    public function test_pem_file_generated()
    {
        Storage::fake();

        $path = str_replace('\\', '//', Storage::path('jwk.pem'));

        Storage::assertMissing('jwk.pem');

        $this
            ->artisan(sprintf('littlejwt:pem "%s" --display', $path))
                ->expectsOutput('Generated environment variables:')
                ->assertExitCode(0);

        Storage::assertExists('jwk.pem');
    }

    /**
     * Tests that a .pem file is not overwritten using the littlejwt:pem command.
     *
     * @return void
     */
    public function test_pem_file_not_overwritten()
    {
        Storage::fake();

        $existing = $this->faker->text;
        Storage::put('jwk.pem', $existing);

        $path = str_replace('\\', '//', Storage::path('jwk.pem'));

        Storage::assertExists('jwk.pem');

        $this
            ->artisan(sprintf('littlejwt:pem "%s" --display', $path))
                ->assertExitCode(1);

        $this->assertStringEqualsFile($path, $existing);
    }

    /**
     * Tests that a .pem file is not overwritten using the littlejwt:pem command.
     *
     * @return void
     */
    public function test_pem_file_overwritten()
    {
        Storage::fake();

        $existing = $this->faker->text;

        Storage::put('jwk.pem', $existing);

        $path = str_replace('\\', '//', Storage::path('jwk.pem'));

        Storage::assertExists('jwk.pem');

        $this
            ->artisan(sprintf('littlejwt:pem "%s" --display --force', $path))
                ->assertExitCode(0)
                ->run();

        $this->assertStringNotEqualsFile($path, $existing);
    }

    /**
     * Tests the JWT is created and validated using a JWK secret.
     *
     * @return void
     */
    public function test_create_validate_jwk_secret()
    {
        $phrase = Base64Encoder::encode($this->faker->sha1());
        $jwk = $this->app[Keyable::class]->buildFromSecret(['phrase' => $phrase]);

        LittleJWT::fake($jwk);

        $passes = $this->createValidateWithJwk($jwk);

        $this->assertTrue($passes);
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
        $jwk = $this->app[Keyable::class]->buildFromSecret(['phrase' => $phrase]);

        LittleJWT::fake($jwk);

        $token = LittleJWT::createToken(null, false);

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
        $jwk = $this->app[Keyable::class]->buildFromSecret(['phrase' => $phrase]);

        LittleJWT::fake($jwk);

        $token = LittleJWT::createToken();
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
        $this->useAlgorithm(\Jose\Component\Signature\Algorithm\RS256::class);

        Storage::fake();

        $openssl = $this->app[OpenSSLBuilder::class];

        Storage::put('jwk.key', $openssl->exportPrivateKey($openssl->generatePrivateKey()));

        $config = [
            'type' => KeyBuilder::KEY_FILES_PEM,
            'path' => Storage::path('jwk.key'),
            'secret' => '',
        ];

        $jwk = $this->app[Keyable::class]->buildFromFile($config);

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
        $this->useAlgorithm(\Jose\Component\Signature\Algorithm\RS256::class);

        Storage::fake();

        $openssl = $this->app[OpenSSLBuilder::class];
        $privKey = $openssl->exportPrivateKey($openssl->generatePrivateKey());

        $jwk = $this->app[Keyable::class]->createFromKey($privKey);

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
        $this->useAlgorithm(\Jose\Component\Signature\Algorithm\RS256::class);

        Storage::fake();

        $openssl = $this->app[OpenSSLBuilder::class];

        $privKey = $openssl->generatePrivateKey();
        $csr = $openssl->generateCertificateSignRequest($this->faker->domainName(), $privKey);
        $crt = $openssl->generateCertificate($csr, $privKey);

        Storage::put('jwk.p12', $openssl->exportPkcs12($crt, $privKey));

        $jwk = $this->app[Keyable::class]->createFromPKCS12CertificateFile(Storage::path('jwk.p12'));

        $this->assertTrue($this->createValidateWithJwk($jwk));
    }

    /**
     * Tests the InvalidHashAlgorithmException is thrown when algorithm isn't set.
     *
     * @return void
     */
    public function test_invalid_hash_algorithm_thrown()
    {
        $this->expectException(InvalidHashAlgorithmException::class);

        config()->set('littlejwt.key.algorithm', null);

        LittleJWT::createToken();
    }

    /**
     * Creates and validates a JWT with the same JWK
     *
     * @param JWK $jwk JWK to use to create and validate token.
     * @return bool True if JWT is valid.
     */
    protected function createValidateWithJwk(JWK $jwk)
    {
        LittleJWT::fake($jwk);

        $canary = $this->faker->uuid();

        $jwt = LittleJWT::createJwt(function (Builder $builder) use ($canary) {
            $builder->can($canary);
        });

        return LittleJWT::validateJWT($jwt, function (TestValidator $validator) use ($canary) {
            $validator
                ->assertPasses()
                ->assertClaimMatches('can', $canary);
        });
    }
}
