<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LittleApps\LittleJWT\Build\Build;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\Exceptions\InvalidClaimValueException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\JWK\JWKValidator;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;
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
        $token = LittleJWT::create(function (Builder $builder) {
            $builder->withDefaults();
        });

        $jwt = LittleJWT::parse((string) $token);

        $this->assertEquals($token, (string) $jwt);
    }

    /**
     * Tests creating a JWT
     *
     * @return void
     */
    public function test_create_default_jwt()
    {
        $jwt = LittleJWT::create();

        foreach (['alg'] as $key) {
            $this->assertArrayHasKey($key, $jwt->getHeaders());
        }

        foreach (['iat', 'nbf', 'exp', 'iss', 'jti'] as $key) {
            $this->assertArrayHasKey($key, $jwt->getPayload());
        }

        $this->assertInstanceOf(JsonWebToken::class, $jwt);
    }

    /**
     * Tests creating a signed JWT
     *
     * @return void
     */
    public function test_create_default_signed_jwt()
    {
        $jwt = LittleJWT::create();

        $this->assertInstanceOf(SignedJsonWebToken::class, $jwt);
        $this->assertNotEmpty($jwt->getSignature());
    }

    /**
     * Tests creating a signed JWT
     *
     * @return void
     */
    public function test_create_default_auto_unsigned_jwt()
    {
        $jwt = LittleJWT::autoSign(false)->create();

        $this->assertInstanceOf(JsonWebToken::class, $jwt);
        $this->assertNotInstanceOf(SignedJsonWebToken::class, $jwt);
    }

    /**
     * Tests creating a signed JWT
     *
     * @return void
     */
    public function test_create_default_auto_signed_jwt()
    {
        $jwt = LittleJWT::autoSign(true)->create();

        $this->assertInstanceOf(SignedJsonWebToken::class, $jwt);
        $this->assertNotEmpty($jwt->getSignature());
    }

    /**
     * Tests using Build to create a signed JWT
     *
     * @return void
     */
    public function test_build_empty_jwt()
    {
        $jwt = LittleJWT::create(function (Builder $builder) {
            $builder->withoutDefaults();
        });

        $this->assertCount(0, $jwt->getHeaders());
        $this->assertCount(0, $jwt->getPayload());

        $this->assertInstanceOf(JsonWebToken::class, $jwt);
    }

    /**
     * Tests using Build to create a JWT with custom claims.
     *
     * @return void
     */
    public function test_build_custom_jwt()
    {
        $build = LittleJWT::build();

        $header = [$this->faker->word, $this->faker->uuid];
        $payload = [$this->faker->word, $this->faker->uuid];

        $build->passBuilderThru(function (Builder $builder) use ($header, $payload) {
            $builder
                ->withoutDefaults()
                ->addHeaderClaim($header[0], $header[1])
                ->addPayloadClaim($payload[0], $payload[1]);
        });

        $jwt = $build->build();

        $this->assertCount(1, $jwt->getHeaders());
        $this->assertEquals($header[1], $jwt->getHeaders()->get($header[0]));

        $this->assertCount(1, $jwt->getPayload());
        $this->assertEquals($payload[1], $jwt->getPayload()->get($payload[0]));

        $this->assertInstanceOf(Build::class, $build);
        $this->assertInstanceOf(JsonWebToken::class, $jwt);
    }

    /**
     * Tests a JWT has default claims.
     *
     * @return void
     */
    public function test_parse_default_claims()
    {
        $token = LittleJWT::create();

        $jwt = LittleJWT::parse((string) $token);

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

        $token = LittleJWT::create(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        });

        $jwt = LittleJWT::parse((string) $token);

        $this->assertNotNull($jwt->getPayload()->get('sub'));
        $this->assertEquals($sub, $jwt->getPayload()->get('sub'));
    }

    /**
     * Tests claims use the date format specified in the RFC (https://www.rfc-editor.org/rfc/rfc7519#section-2)
     *
     * @return void
     */
    public function test_numeric_date_format()
    {
        $expectedDateTime = Carbon::now()->addDay();

        $token = LittleJWT::create(function (Builder $builder) use ($expectedDateTime) {
            $builder->exp($expectedDateTime);
        });

        $jwt = LittleJWT::parse((string) $token);

        $this->assertTrue(Carbon::createFromTimestamp($jwt->getPayload()->get('exp')) !== false);

        $this->assertTrue(is_numeric($jwt->getPayload()->get('exp')));
    }

    /**
     * Tests that the JSON encoding fails when a claim has non-UTF8 characters.
     *
     * @return void
     */
    public function test_claims_has_non_utf8()
    {
        $binary = '';

        for ($i = 0; $i < 100; $i++) {
            $binary .= chr($this->faker->numberBetween(248, 253));
        }

        // Ensures value doesn't have printable characters
        $this->assertFalse(mb_detect_encoding($binary, null, true));

        $this->expectException(InvalidClaimValueException::class);

        LittleJWT::create(function (Builder $builder) use ($binary) {
            $builder->bin($binary);
        });
    }

    /**
     * Tests parsing a JWT that doesn't have 3 parts.
     *
     * @return void
     */
    public function test_parse_missing_part()
    {
        $jwt = LittleJWT::create();
        $token = implode('.', array_slice(explode('.', (string) $jwt), 0, 1));

        $this->expectException(CantParseJWTException::class);

        LittleJWT::parse((string) $token, true);
    }

    /**
     * Tests parsing a JWT that doesn't have an array/object as the header.
     *
     * @return void
     */
    public function test_parse_invalid_header()
    {
        $jwt = LittleJWT::create();
        $parts = explode('.', (string) $jwt);
        $token = implode('.', ['foo', $parts[1], $parts[2]]);

        $this->expectException(CantParseJWTException::class);

        LittleJWT::parse((string) $token, true);
    }

    /**
     * Tests parsing a JWT that doesn't have an array/object as the payload.
     *
     * @return void
     */
    public function test_parse_invalid_payload()
    {
        $jwt = LittleJWT::create();
        $parts = explode('.', (string) $jwt);
        $token = implode('.', [$parts[0], 'foo', $parts[2]]);

        $this->expectException(CantParseJWTException::class);

        LittleJWT::parse((string) $token, true);
    }

    /**
     * Tests a claim in the builder is set.
     *
     * @return void
     */
    public function test_builder_claim_isset()
    {
        $sub = $this->faker->uuid;

        LittleJWT::create(function (Builder $builder) use ($sub) {
            $builder->sub($sub);

            $this->assertTrue(isset($builder->sub));
            $this->assertFalse(isset($builder->foo));
        });
    }
}
