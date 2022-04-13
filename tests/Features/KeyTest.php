<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;

use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Keyable;
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
