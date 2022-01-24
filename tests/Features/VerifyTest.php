<?php

namespace LittleApps\LittleJWT\Tests\Features;

use LittleApps\LittleJWT\Tests\TestCase;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\KeyBuildable;
use LittleApps\LittleJWT\Facades\Blacklist;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\JWT\Rules;
use LittleApps\LittleJWT\Testing\TestVerifier;
use LittleApps\LittleJWT\Utils\Base64Encoder;

class VerifyTest extends TestCase
{
    use WithFaker, InteractsWithLittleJWT;

    /**
     * Tests that a default JWT is valid.
     *
     * @return void
     */
    public function test_empty_jwt_valid()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::createJWT();

        LittleJWT::verifyJWT($jwt, function (TestVerifier $verifier) {
            $verifier
                ->assertPasses();
        });
    }

    /**
     * Tests that a JWT has a sub.
     *
     * @return void
     */
    public function test_jwt_valid()
    {
        LittleJWT::fake();

        $sub = $this->faker->uuid;

        $jwt = LittleJWT::createJWT(function(Builder $builder) use ($sub) {
            $builder->sub($sub);
        });

        LittleJWT::verifyJWT($jwt, function (TestVerifier $verifier) use ($sub) {
            $verifier
                ->assertPasses()
                ->assertClaimMatches('sub', $sub);
        });
    }

    /**
     * Tests that a JWT is expired.
     *
     * @return void
     */
    public function test_jwt_expired() {
        LittleJWT::fake();

        $jwt = LittleJWT::createJWT(function(Builder $builder) {
            $builder->exp(Carbon::now()->subMonth());
        });

        LittleJWT::verifyJWT($jwt, function (TestVerifier $verifier) {
            $verifier
                ->assertFails()
                ->assertExpired()
                ->assertErrorCount(1)
                ->assertErrorKeyExists('exp');
        });
    }

    /**
     * Tests that a JWT has an invalid signature.
     *
     * @return void
     */
    public function test_jwt_invalid_signature() {
        LittleJWT::fake();

        $otherJWk = $this->app->make(KeyBuildable::class)->build([
            KeyBuilder::KEY_SECRET => [
                'phrase' => Base64Encoder::encode($this->faker->sha256)
            ]
        ]);

        $jwt = LittleJWT::withJwk($otherJWk)->createJWT();

        LittleJWT::verifyJWT($jwt, function (TestVerifier $verifier) {
            $verifier
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
    public function test_jwt_blacklisted() {
        LittleJWT::fake();

        $jwt = LittleJWT::createJWT();

        Blacklist::blacklist($jwt);

        LittleJWT::verifyJWT($jwt, function (TestVerifier $verifier) {
            $verifier
                ->assertNotAllowed()
                ->assertFails()
                ->assertErrorCount(1)
                ->assertErrorKeyExists(Rules\Allowed::class);
        });
    }
}