<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Keyable;
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
    use WithFaker;
    use InteractsWithLittleJWT;

    /**
     * Tests that a default JWT is valid.
     *
     * @return void
     */
    public function test_empty_jwt_valid()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::createJWT();

        LittleJWT::validateJWT($jwt, function (TestValidator $validator) {
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

        $jwt = LittleJWT::createToken();

        LittleJWT::validateToken($jwt, function (TestValidator $validator) {
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

        $jwt = LittleJWT::createJWT(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        });

        LittleJWT::validateJWT($jwt, function (TestValidator $validator) use ($sub) {
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

        $jwt = LittleJWT::createToken(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        });

        LittleJWT::validateToken($jwt, function (TestValidator $validator) use ($sub) {
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

        $jwt = LittleJWT::createJWT(function (Builder $builder) {
            $builder->exp(Carbon::now()->subMonth());
        });

        LittleJWT::validateJWT($jwt, function (TestValidator $validator) {
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

        $jwt = LittleJWT::createJWT();

        LittleJWT::validateJWT($jwt, function (TestValidator $validator) {
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

        $token = LittleJWT::createToken();

        LittleJWT::validateToken($token, function (TestValidator $validator) {
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

        $otherJWk = $this->app->make(Keyable::class)->build([
            KeyBuilder::KEY_SECRET => [
                'phrase' => Base64Encoder::encode($this->faker->sha256),
            ],
        ]);

        $jwt = LittleJWT::withJwk($otherJWk)->createJWT();

        LittleJWT::validateJWT($jwt, function (TestValidator $validator) {
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

        $jwt = LittleJWT::createJWT();

        Blacklist::blacklist($jwt);

        LittleJWT::validateJWT($jwt, function (TestValidator $validator) {
            $validator
                ->assertNotAllowed()
                ->assertFails()
                ->assertErrorCount(1)
                ->assertErrorKeyExists(Rules\Allowed::class);
        });
    }
}
