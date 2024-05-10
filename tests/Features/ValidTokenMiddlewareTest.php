<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Concerns\JWTHelpers;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class ValidTokenMiddlewareTest extends TestCase
{
    use CreatesUser;
    use InteractsWithLittleJWT;
    use JWTHelpers;
    use WithFaker;

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
        $jwt = LittleJWT::create(function (Builder $builder) {
            $builder->exp(Carbon::now()->subMonth());
        })->sign();

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
        $validJwt = LittleJWT::create()->sign();

        $signature = random_bytes(10);

        $mutators = $this->app->config->get('littlejwt.builder.mutators', ['header' => [], 'payload' => []]);

        $invalidJwt =
            LittleJWT::createJWTBuilder()
                ->buildFromParts($validJwt->getHeaders()->toArray(), $validJwt->getPayload()->toArray(), $signature);

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
        $jwt = LittleJWT::create(function (Builder $builder) {
            if ($this->faker->boolean) {
                $builder->sub($this->user->getAuthIdentifier());
            } else {
                $builder->prv($this->hashSubjectModel($this->user));
            }
        })->sign();

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
        $jwt = LittleJWT::create()->sign();

        $response = $this
            ->withJwt($jwt)
            ->getJson('/api/middleware/guard');

        $response
            ->assertUnauthorized();
    }
}
