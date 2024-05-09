<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use LittleApps\LittleJWT\Tests\Concerns\CreatesEnvFile;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class CommandTest extends TestCase
{
    use CreatesEnvFile;
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
     * Tests JWT phrase is generated and set for new .env key.
     *
     * @return void
     */
    public function test_secret_phrase_generated_for_new_key() {
        $this->createEnvFile();

        $this->artisan('littlejwt:phrase', ['--key' => 'ABC_XYZ', '--yes' => true])
            ->assertSuccessful();

        $this->reloadEnv()
            ->assertEnvSet('ABC_XYZ');
    }

    /**
     * Tests JWT phrase is generated and set for existing .env key.
     *
     * @return void
     */
    public function test_secret_phrase_generated_for_existing_key() {
        $existing = $this->faker()->uuid();

        $this->createEnvFileWithExisting([
            'ABC_XYZ' => $existing
        ]);

        $this->reloadEnv()
            ->artisan('littlejwt:phrase', ['--key' => 'ABC_XYZ'])
            ->expectsConfirmation('Overwrite existing JWT secret in .env file?', 'yes')
            ->assertSuccessful();

        $this->reloadEnv()
            ->assertEnvNotEquals('ABC_XYZ', $existing);
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
}
