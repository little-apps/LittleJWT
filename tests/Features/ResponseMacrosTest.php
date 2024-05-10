<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Guards\Adapters\GenericAdapter;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class ResponseMacrosTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    use CreatesUser;
    use InteractsWithLittleJWT;

    protected function setUp(): void
    {
        parent::setUp();

        Auth::setAdapter($this->app[GenericAdapter::class]);
    }

    /**
     * Tests request 'getJwt' macro is functioning.
     *
     * @return void
     */
    public function test_get_jwt()
    {
        $jwt = LittleJWT::create();

        $response =
            $this
                ->withJwt($jwt)
                ->getJson('/api/io/jwt');

        $response
            ->assertOk()
            ->assertHasJWT();
    }

    /**
     * Tests that a JWT is included in response for login.
     *
     * @return void
     */
    public function test_valid_login_json()
    {
        $response = $this->postJson('/api/login/response', [
            'email' => $this->user->email,
            'password' => $this->getCurrentPassword(),
        ]);

        $response
            ->assertOk()
            ->assertHasJWT()
            ->assertHeader('Authorization');
    }

    /**
     * Tests that a JWT isn't included in response for login.
     *
     * @return void
     */
    public function test_invalid_login_json()
    {
        $response = $this->postJson('/api/login/response', [
            'email' => $this->user->email,
            'password' => $this->faker->password,
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson(['status' => 'error'])
            ->assertHeaderMissing('Authorization');
    }

    /**
     * Tests that a response includes the JWT.
     *
     * @return void
     */
    public function test_login_response_trait_jwt()
    {
        $response = $this->postJson('/api/login/response/trait', [
            'email' => $this->user->email,
            'password' => $this->getCurrentPassword(),
            'build' => 'jwt',
        ]);

        $response
            ->assertOk()
            ->assertHasJWT();
    }

    /**
     * Tests that a response includes the token.
     *
     * @return void
     */
    public function test_login_response_trait_token()
    {
        $response = $this->postJson('/api/login/response/trait', [
            'email' => $this->user->email,
            'password' => $this->getCurrentPassword(),
            'build' => 'token',
        ]);

        $response
            ->assertOk()
            ->assertHasJWT();
    }

    /**
     * Tests that a JWT is attached to response as Authorization header.
     *
     * @return void
     */
    public function test_login_response_trait_header()
    {
        $response = $this->postJson('/api/login/response/trait', [
            'email' => $this->user->email,
            'password' => $this->getCurrentPassword(),
            'attach' => 'header',
        ]);

        $response
            ->assertOk()
            ->assertHeader('Authorization');
    }
}
