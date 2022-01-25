<?php

namespace LittleApps\LittleJWT\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use LittleApps\LittleJWT\ServiceProvider;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LittleApps\\LittleJWT\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        parent::setUp();

        Auth::shouldUse('jwt');
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[Concerns\CreatesUser::class])) {
            $this->setUpUser();
        }

        if (isset($uses[Concerns\InteractsWithLittleJWT::class])) {
            $this->setUpLittleJwt();
        }

        return $uses;
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     *
     * @return void
     */
    protected function defineRoutes($router)
    {
        $router
            ->prefix('api')
            ->group(function ($router) {
                $router->post('/login', function (Request $request) {
                    $credentials = $request->validate([
                        'email' => ['required', 'email'],
                        'password' => ['required'],
                    ]);

                    if (Auth::validate($credentials)) {
                        return Auth::createJwtResponse(Auth::user());
                    }

                    return Response::json([
                        'status' => 'error',
                        'message' => 'The provided credentials do not match our records.',
                    ], 401);
                });

                $router->middleware('auth:jwt')->get('/user', function (Request $request) {
                    return $request->user();
                });
            });
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    public function defineEnvironment($app)
    {
        $migration = include __DIR__.'/../database/migrations/create_jwt_blacklist_table.php.stub';
        $migration->up();

        $littlejwt = include __DIR__.'/../config/littlejwt.php';
        config()->set('littlejwt', $littlejwt);

        config()->set('database.default', 'testing');
        config()->set('auth.defaults.guard', 'jwt');
        config()->set('auth.guards.jwt', [
            'driver' => 'littlejwt',
            'adapter' => 'fingerprint',
            'provider' => 'users',
            /**
            * The input key in the request to use.
            */
            'input_key' => 'token',

            'adapters' => [
                'generic' => [
                    'adapter' => \LittleApps\LittleJWT\Guards\Adapters\GenericAdapter::class,
                    /**
                    * The model used for JWT authentication.
                    * NOTE: Setting this to false is will cause models in JWT to not be verified. This is NOT recommended.
                    */
                    'model' => \LittleApps\LittleJWT\Testing\Models\User::class,
                ],
                'fingerprint' => [
                    'adapter' => \LittleApps\LittleJWT\Guards\Adapters\FingerprintAdapter::class,
                    /**
                    * Name of the cookie to hold the fingerprint.
                    */
                    'cookie' => 'fingerprint',
                    'ttl' => 0,
                ],
            ],
        ]);
    }
}
