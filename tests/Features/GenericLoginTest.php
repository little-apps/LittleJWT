<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

use LittleApps\LittleJWT\Guards\Adapters\GenericAdapter;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class GenericLoginTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    use CreatesUser;
    use InteractsWithLittleJWT;

    protected function setUp(): void
    {
        parent::setUp();

        $adapter = $this->app[GenericAdapter::class];
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
            ->assertHasJWT();
    }

    /**
     * Test the user is retrieved with a valid generic JWT.
     *
     * @return void
     */
    public function test_get_user()
    {
        $response = $this
            ->withAuthenticatable($this->user)
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJson($this->user->toArray());
    }

    public function test_jwt_other_user()
    {
        $otherUser = $this->createUser();

        $response = $this
            ->withAuthenticatable($otherUser)
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJson($otherUser->toArray())
            ->assertJsonMissing(Arr::only($this->user->toArray(), ['email', 'name']));
    }
}
