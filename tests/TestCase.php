<?php

namespace LittleApps\LittleJWT\Tests;

use Illuminate\Support\Facades\Auth;
use LittleApps\LittleJWT\ServiceProvider;
use LittleApps\LittleJWT\Testing\Models\User;
use LittleApps\LittleJWT\Testing\TestController;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use WithWorkbench;
    use WithLaravelMigrations;

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
                $router->any('/io', [TestController::class, 'testIo']);
                $router->get('/io/jwt', [TestController::class, 'testGetJwt']);

                $router->post('/login', [TestController::class, 'testLogin']);
                $router->post('/login/response', [TestController::class, 'testLoginResponse']);
                $router->post('/login/response/trait', [TestController::class, 'testResponseTrait']);

                $router->middleware('auth:jwt')->get('/user', [TestController::class, 'testUser']);

                $router->middleware(\LittleApps\LittleJWT\Laravel\Middleware\ValidToken::class)->get('/middleware', [TestController::class, 'testMiddleware']);

                $router->middleware('validtoken:guard')->get('/middleware/guard', [TestController::class, 'testMiddleware']);
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
