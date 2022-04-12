<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\Build\Build;
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

        foreach (['alg'] as $key) {
            $this->assertArrayHasKey($key, $jwt->getHeaders());
        }

        foreach (['iat', 'nbf', 'exp', 'iss', 'jti'] as $key) {
            $this->assertArrayHasKey($key, $jwt->getPayload());
        }

        $this->assertInstanceOf(JWT::class, $jwt);
    }

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
     * Tests using Build to create a JWT with custom claims.
     *
     * @return void
     */
    public function test_build_custom_jwt()
    {
        $build = LittleJWT::buildJWT();

        $header = [$this->faker->word, $this->faker->uuid];
        $payload = [$this->faker->word, $this->faker->uuid];

        $jwt =
            $build
                ->addHeaderClaim($header[0], $header[1])
                ->addPayloadClaim($payload[0], $payload[1])
                ->build();

        $this->assertCount(1, $jwt->getHeaders());
        $this->assertEquals($header[1], $jwt->getHeaders()->get($header[0]));

        $this->assertCount(1, $jwt->getPayload());
        $this->assertEquals($payload[1], $jwt->getPayload()->get($payload[0]));

        $this->assertInstanceOf(Build::class, $build);
        $this->assertInstanceOf(JWT::class, $jwt);
    }

    /**
     * Tests a JWT has default claims.
     *
     * @return void
     */
    public function test_parse_default_claims()
    {
        $token = LittleJWT::createToken();

        $jwt = LittleJWT::parseToken($token);

        foreach (['alg'] as $key) {
            $this->assertArrayHasKey($key, $jwt->getHeaders());
        }

        foreach (['iss', 'iat', 'exp', 'nbf'] as $key) {
            $this->assertArrayHasKey($key, $jwt->getPayload());
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

    /**
     * Tests claims use the date format specified in the RFC (https://www.rfc-editor.org/rfc/rfc7519#section-2)
     *
     * @return void
     */
    public function test_numeric_date_format() {
        $expectedDateTime = Carbon::now()->addDay();

        $token = LittleJWT::createToken(function (Builder $builder) use($expectedDateTime) {
            $builder->exp($expectedDateTime);
        });

        $jwt = LittleJWT::parseToken($token);

        $this->assertTrue(Carbon::createFromFormat('U', $jwt->getPayload()->get('exp')) !== false);

        $this->assertTrue(is_numeric($jwt->getPayload()->get('exp')));
    }
}
