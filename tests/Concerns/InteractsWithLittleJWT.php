<?php

namespace LittleApps\LittleJWT\Tests\Concerns;

use App\Classes\LittleJWT\JWT\JWT as JWTPayload;

use LittleApps\LittleJWT\Factories\JWTHasher;

use Illuminate\Contracts\Auth\Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;

trait InteractsWithLittleJWT
{
    /**
     * Assigns JWTAuth instance to jwtAuth field.
     *
     * @return void
     */
    protected function setUpLittleJwt()
    {
        Auth::shouldUse('jwt');

        // Create response test for JWT response.
        TestResponse::macro('assertHasJWT', function () {
            return $this->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'expires_at']);
        });

        TestResponse::macro('assertIsJWT', function ($jwt) {
            return $this->assertJson(['access_token' => (string) $jwt]);
        });
    }

    /**
     * Includes JWT in HTTP requests
     *
     * @param string|JWTPayload $token
     * @return $this
     */
    public function withJwt($token)
    {
        return $this->withToken((string) $token);
    }

    /**
     * Attaches a JWT from an authenticatable.
     *
     * @param Authenticatable $authenticatable
     * @return $this
     */
    public function withAuthenticatable(Authenticatable $authenticatable)
    {
        $jwt = Auth::buildJwtForUser($authenticatable);

        $this->withJwt($jwt);

        return $this;
    }

    /**
     * Set the currently logged in user for the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $driver
     * @return $this
     */
    public function actingAs(Authenticatable $user, $driver = null)
    {
        $this->withAuthenticatable($user);

        $this->app['auth']->guard($driver)->setUser($user);
        $this->app['auth']->shouldUse($driver);

        return $this;
    }

    /**
     * Specifies the algorithm to use
     *
     * @param string $algorithm Fully qualified class name
     * @return $this
     */
    protected function useAlgorithm(string $algorithm) {
        $this->app->singleton(JWTHasher::class, function ($app) use ($algorithm) {
            return new JWTHasher($app->make($algorithm));
        });

        return $this;
    }
}
