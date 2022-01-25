<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;

use LittleApps\LittleJWT\Guards\Adapters\FingerprintAdapter;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class FingerprintLoginTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    use CreatesUser;
    use InteractsWithLittleJWT;

    protected function setUp(): void
    {
        parent::setUp();

        $adapter = $this->app[FingerprintAdapter::class];
        Auth::setAdapter($adapter);
    }

    /**
     * Tests that a JWT is created for valid credentials.
     *
     * @return void
     */
    public function test_valid_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => $this->getCurrentPassword(),
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'expires_at'])
            ->assertCookie('fingerprint');
    }

    /**
     * Tests that a JWT is not created for invalid credentials.
     *
     * @return void
     */
    public function test_invalid_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => $this->faker->password,
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson(['status' => 'error']);
    }

    /**
     * Tests the fingerprint cookie value doesn't match what's in JWT.
     *
     * @return void
     */
    public function test_invalid_fingerprint_cookie()
    {
        $fingerprint = (string) $this->faker->uuid;

        $invalidJwt = Auth::buildJwtForUser($this->user);

        $response = $this
            ->withJwt($invalidJwt)
            ->withUnencryptedCookie(Auth::getFingerprintCookieName(), $fingerprint)
            ->getJson('/api/user');

        $response
            ->assertUnauthorized();
    }

    /**
     * Tests the fingerprint cookie is missing but exists in JWT.
     *
     * @return void
     */
    public function test_missing_fingerprint_cookie()
    {
        $fingerprint = $this->createFingerprint();

        $response = $this
            ->withFingerprintJwt($this->user, $this->createFingerprintHash($fingerprint))
            ->getJson('/api/user');

        $response
            ->assertUnauthorized();
    }

    /**
     * Tests the user is retrieved with a valid JWT.
     *
     * @return void
     */
    public function test_get_user()
    {
        $fingerprint = $this->createFingerprint();

        $response = $this
            ->withFingerprintJwt($this->user, $this->createFingerprintHash($fingerprint))
            ->withFingerprintCookie($fingerprint)
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJson($this->user->toArray());
    }

    /**
     * Test the user is retrieved with a valid JWT and no fingerprint.
     *
     * @return void
     */
    public function test_get_user_no_fingerprint()
    {
        $response = $this
            ->withAuthenticatable($this->user)
            ->getJson('/api/user');

        $response
            ->assertUnauthorized();
    }

    protected function createFingerprint()
    {
        return Auth::createFingerprint();
    }

    protected function createFingerprintHash(string $fingerprint)
    {
        return Auth::hashFingerprint($fingerprint);
    }

    protected function withFingerprintJwt(Authenticatable $authenticatable, string $fingerprintHash)
    {
        $jwt = Auth::createJwtWithFingerprint($authenticatable, $fingerprintHash);

        return $this->withJwt($jwt);
    }

    protected function withFingerprintCookie(string $fingerprint)
    {
        return $this
            ->withCredentials() // Needs to called in order for cookies to work.
            ->withUnencryptedCookie(Auth::getFingerprintCookieName(), $fingerprint);
    }
}
