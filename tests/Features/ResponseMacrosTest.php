<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;

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
}
