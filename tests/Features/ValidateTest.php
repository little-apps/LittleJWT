<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Facades\Blacklist;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\JWT\Rules;
use LittleApps\LittleJWT\Testing\TestValidator;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class ValidateTest extends TestCase
{
    use InteractsWithLittleJWT;
    use WithFaker;

    /**
     * Tests that a default JWT is valid.
     *
     * @return void
     */
    public function test_empty_jwt_defaults()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->withDefaults()
                ->assertPasses();
        });
    }

    /**
     * Tests that a default JWT is invalid.
     *
     * @return void
     */
    public function test_empty_jwt_defaults_invalid()
    {
        LittleJWT::fake();

        $otherJWk = KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            KeyBuilder::KEY_SECRET => [
                'phrase' => Base64Encoder::encode($this->faker->sha256),
            ],
        ]);

        $jwt = LittleJWT::withJwk($otherJWk)->create();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->withDefaults()
                ->assertFails();
        });
    }

    /**
     * Tests that a default JWT is valid.
     *
     * @return void
     */
    public function test_empty_jwt_valid()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertPasses();
        });
    }

    /**
     * Tests that a default JWT is valid.
     *
     * @return void
     */
    public function test_empty_jwt_validate_token()
    {
        LittleJWT::fake();

        $token = (string) LittleJWT::createSigned();

        LittleJWT::validateToken($token, function (TestValidator $validator) {
            $validator
                ->assertPasses();
        });
    }

    /**
     * Tests that a default token is valid.
     *
     * @return void
     */
    public function test_empty_token_valid()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertPasses();
        });
    }

    /**
     * Tests that a JWT has a sub.
     *
     * @return void
     */
    public function test_jwt_sub_valid()
    {
        LittleJWT::fake();

        $sub = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        })->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($sub) {
            $validator
                ->assertPasses()
                ->assertClaimMatches('sub', $sub);
        });
    }

    /**
     * Tests that a token has a sub.
     *
     * @return void
     */
    public function test_token_sub_valid()
    {
        LittleJWT::fake();

        $sub = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        })->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($sub) {
            $validator
                ->assertPasses()
                ->assertClaimMatches('sub', $sub);
        });
    }

    /**
     * Tests that a JWT is expired.
     *
     * @return void
     */
    public function test_jwt_expired()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create(function (Builder $builder) {
            $builder->exp(Carbon::now()->subMonth());
        })->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertFails()
                ->assertFutureFails('exp')
                ->assertErrorCount(1)
                ->assertErrorKeyExists('exp');
        });
    }

    /**
     * Tests a claim doesn't exist in JWT.
     *
     * @return void
     */
    public function test_jwt_missing_claim()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertPasses()
                ->assertCustomClaimPasses('abc', function ($value) {
                    // Should not reach here because claim 'abc' doesn't exist.
                    return false;
                });
        });
    }

    /**
     * Tests a claim doesn't exist in token.
     *
     * @return void
     */
    public function test_token_missing_claim()
    {
        LittleJWT::fake();

        $token = (string) LittleJWT::create()->sign();

        $jwt = LittleJWT::parse($token);

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertFails()
                ->assertClaimsDoesntExist(['abc']);
        });
    }

    /**
     * Tests that a JWT has an invalid signature.
     *
     * @return void
     */
    public function test_jwt_invalid_signature()
    {
        LittleJWT::fake();

        $otherJWk = KeyBuilder::buildFromConfig([
            KeyBuilder::KEY_SECRET => [
                'phrase' => Base64Encoder::encode($this->faker->sha256),
            ],
        ]);

        $jwt = LittleJWT::withJwk($otherJWk)->create();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertInvalidSignature()
                ->assertFails()
                ->assertErrorCount(1)
                ->assertErrorKeyExists(Rules\ValidSignature::class);
        });
    }

    /**
     * Tests that a JWT is blacklisted.
     *
     * @return void
     */
    public function test_jwt_blacklisted()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        Blacklist::blacklist($jwt);

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertNotAllowed()
                ->assertFails()
                ->assertErrorCount(1)
                ->assertErrorKeyExists(Rules\Allowed::class);
        });
    }

    /**
     * Tests a claim value is one of expected values.
     *
     * @return void
     */
    public function test_jwt_oneof()
    {
        LittleJWT::fake();

        $actual = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($actual) {
            $builder->foo($actual);
        })->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($actual) {
            $validator
                ->assertPasses()
                ->oneOf('foo', [
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $actual,
                ]);
        });
    }

    /**
     * Tests a claim value is one of expected values.
     *
     * @return void
     */
    public function test_jwt_not_oneof()
    {
        LittleJWT::fake();

        $actual = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($actual) {
            $builder->foo($actual);
        })->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertFails()
                ->oneOf('foo', [
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                ]);
        });
    }

    /**
     * Tests a claim value has some of expected values in array.
     *
     * @return void
     */
    public function test_jwt_arrayequals_loose()
    {
        LittleJWT::fake();

        $actual = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($actual) {
            $builder->foo([$actual, $this->faker->uuid]);
        })->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($actual) {
            $validator
                ->assertPasses()
                ->arrayEquals('foo', [
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $actual,
                ]);
        });
    }

    /**
     * Tests a claim value has all expected values in array.
     *
     * @return void
     */
    public function test_jwt_arrayequals_strict()
    {
        LittleJWT::fake();

        $actual = [
            $this->faker->uuid,
            $this->faker->uuid,
            $this->faker->uuid,
        ];

        $jwt = LittleJWT::create(function (Builder $builder) use ($actual) {
            $builder->foo($actual);
        })->sign();

        $expected = $actual;

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($expected) {
            shuffle($expected);

            $validator
                ->assertPasses()
                ->arrayEquals('foo', $expected, true);
        });
    }

    /**
     * Tests a claim value doesn't have some of expected values in array.
     *
     * @return void
     */
    public function test_jwt_not_arrayequals_loose()
    {
        LittleJWT::fake();

        $actual = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($actual) {
            $builder->foo([$actual, $this->faker->uuid]);
        })->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertFails()
                ->arrayEquals('foo', [
                    $this->faker->uuid,
                    $this->faker->uuid,
                    $this->faker->uuid,
                ]);
        });
    }

    /**
     * Tests a claim value doesn't have all expected values in array.
     *
     * @return void
     */
    public function test_jwt_not_arrayequals_strict()
    {
        LittleJWT::fake();

        $actual = [
            $this->faker->uuid,
            $this->faker->uuid,
            $this->faker->uuid,
        ];

        $jwt = LittleJWT::create(function (Builder $builder) use ($actual) {
            $builder->foo($actual);
        })->sign();

        $expected = [...$actual, $this->faker->uuid];

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($expected) {
            $validator
                ->assertFails()
                ->arrayEquals('foo', $expected, true);
        });
    }

    /**
     * Tests a JWT validation when JWK phrase is empty.
     *
     * @return void
     */
    public function test_jwt_validation_jwk_phrase_empty()
    {
        $token = (string) LittleJWT::create();

        $jwk = KeyBuilder::buildFromConfig([
            'default' => KeyBuilder::KEY_SECRET,
            'secret' => [
                'phrase' => ''
            ]
        ]);

        LittleJWT::fake($jwk);

        $this->assertFalse(LittleJWT::validateToken($token));
    }
}
