<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Tests\TestCase;

class CreateTest extends TestCase
{
    use WithFaker;

    /**
     * Tests creating a signed JWT
     *
     * @return void
     */
    public function test_create_default_token()
    {
        $token = LittleJWT::createToken();

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals($token, (string) $jwt);
    }

    /**
     * Tests creating a signed JWT
     *
     * @return void
     */
    public function test_create_default_jwt()
    {
        $jwt = LittleJWT::createJWT();

    /**
     * Tests using Build to create a signed JWT
     *
     * @return void
     */
    public function test_build_empty_jwt()
    {
        $build = LittleJWT::buildJWT();

        $jwt = $build->build();

        $this->assertCount(0, $jwt->getHeaders());
        $this->assertCount(0, $jwt->getPayload());

        $this->assertInstanceOf(Build::class, $build);
        $this->assertInstanceOf(JWT::class, $jwt);
    }

    /**
     * Tests a JWT has default claims.
     *
     * @return void
     */
    public function test_has_default_claims()
    {
        $token = LittleJWT::createToken();

        $jwt = LittleJWT::parseToken($token);

        $claims = $jwt->getPayload()->get();

        foreach (['iss', 'iat', 'exp', 'nbf'] as $key) {
            $this->assertArrayHasKey($key, $claims);
        }
    }

    /**
     * Tests a JWT has custom claims.
     *
     * @return void
     */
    public function test_has_custom_claims()
    {
        $sub = $this->faker->uuid;

        $token = LittleJWT::createToken(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        });

        $jwt = LittleJWT::parseToken($token);

        $this->assertNotNull($jwt->getPayload()->get('sub'));
        $this->assertEquals($sub, $jwt->getPayload()->get('sub'));
    }
}
