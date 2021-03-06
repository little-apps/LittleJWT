<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Concerns\HashableSubjectModel;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class ValidTokenMiddlewareTest extends TestCase
{
    use WithFaker;
    use InteractsWithLittleJWT;
    use CreatesUser;
    use HashableSubjectModel;

    /**
     * Test the token is validated by default validator
     *
     * @return void
     */
    public function test_valid_token_default()
    {
        $response = $this
            ->withAuthenticatable($this->user)
            ->getJson('/api/middleware');

        $response
            ->assertOk()
            ->assertJson(['status' => true]);
    }

    /**
     * Test the token is not validated by default validator because it's expired
     *
     * @return void
     */
    public function test_valid_token_default_expired()
    {
        $jwt = LittleJWT::createJWT(function (Builder $builder) {
            $builder->exp(Carbon::now()->subMonth());
        });

        $response = $this
            ->withJwt($jwt)
            ->getJson('/api/middleware');

        $response
            ->assertUnauthorized();
    }

    /**
     * Test the token is not validated by default validator because the signature is invalid.
     *
     * @return void
     */
    public function test_valid_token_default_invalid()
    {
        $validJwt = LittleJWT::createJWT();

        $signature = random_bytes(10);

        $invalidJwt = $this->app[JWTBuilder::class]->buildFromParts($validJwt->getHeaders(), $validJwt->getPayload(), $signature);

        $response = $this
            ->withJwt($invalidJwt)
            ->getJson('/api/middleware');

        $response
            ->assertUnauthorized();
    }

    /**
     * Test the token is validated by guard validator
     *
     * @return void
     */
    public function test_valid_token_guard()
    {
        $response = $this
            ->withAuthenticatable($this->user)
            ->getJson('/api/middleware/guard');

        $response
            ->assertOk()
            ->assertJson(['status' => true]);
    }

    /**
     * Test the token is not validated by guard validator because the subject (sub) claim is invalid.
     *
     * @return void
     */
    public function test_valid_token_guard_invalid_sub()
    {
        $jwt = Auth::buildJwtForUser($this->user, ['sub' => 99999]);

        $response = $this
            ->withJwt($jwt)
            ->getJson('/api/middleware/guard');

        $response
            ->assertUnauthorized();
    }

    /**
     * Test the token is not validated by guard validator because the provider (prv) claim is invalid.
     *
     * @return void
     */
    public function test_valid_token_guard_invalid_prv()
    {
        $jwt = Auth::buildJwtForUser($this->user, ['prv' => $this->faker->sha256]);

        $response = $this
            ->withJwt($jwt)
            ->getJson('/api/middleware/guard');

        $response
            ->assertUnauthorized();
    }

    /**
     * Test the token is not validated by guard validator because the sub or prv claim is missing.
     *
     * @return void
     */
    public function test_valid_token_guard_missing_claim()
    {
        $jwt = LittleJWT::createJWT(function (Builder $builder) {
            if ($this->faker->boolean) {
                $builder->sub($this->user->getAuthIdentifier());
            } else {
                $builder->prv($this->hashSubjectModel($this->user));
            }
        });

        $response = $this
            ->withJwt($jwt)
            ->getJson('/api/middleware/guard');

        $response
            ->assertUnauthorized();
    }

    /**
     * Test the token is not validated by guard validator because the sub and prv claims are missing.
     *
     * @return void
     */
    public function test_valid_token_guard_missing_claims()
    {
        $jwt = LittleJWT::createJWT();

        $response = $this
            ->withJwt($jwt)
            ->getJson('/api/middleware/guard');

        $response
            ->assertUnauthorized();
    }
}
