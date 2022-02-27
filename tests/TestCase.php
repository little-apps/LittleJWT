<?php

namespace LittleApps\LittleJWT\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

use LittleApps\LittleJWT\ServiceProvider;
use LittleApps\LittleJWT\Testing\Models\User;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
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

        $this->withFactories(__DIR__.'/../database/factories/legacy');
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

                $router->middleware(\LittleApps\LittleJWT\Laravel\Middleware\ValidToken::class)->get('/middleware', function (Request $request) {
                    return ['status' => true];
                });

                $router->middleware('validtoken:guard')->get('/middleware/guard', function (Request $request) {
                    return ['status' => true];
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

        config()->set('auth.defaults.guard', 'jwt');
        config()->set('auth.providers.users.model', User::class);
        config()->set('auth.guards.jwt', [
            'driver' => 'littlejwt',
            'adapter' => 'fingerprint',
            'provider' => 'users',
            'input_key' => 'token',
        ]);
    }
}
