<?php

namespace LittleApps\LittleJWT;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFactory;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSBuilder;
use Jose\Easy\Build;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Build\Builders;
use LittleApps\LittleJWT\Commands\GenerateSecretCommand;
use LittleApps\LittleJWT\Contracts\KeyBuildable;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\Guards\Adapters;
use LittleApps\LittleJWT\Guards\Guard;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Utils\ResponseBuilder;
use LittleApps\LittleJWT\Validation\Validators;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('littlejwt')
            ->hasConfigFile()
            ->hasMigration('create_jwt_blacklist_table')
            ->hasCommand(GenerateSecretCommand::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function packageRegistered()
    {
        $this->registerCore();
        $this->registerJoseLibrary();
        $this->registerFactories();
        $this->registerBlacklistManager();
        $this->registerBuilders();
        $this->registerValidators();
        $this->registerGuardAdapters();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function packageBooted()
    {
        $this->bootGuard();
        $this->bootMacros();
    }

    /**
     * Registers the core Little JWT classes.
     *
     * @return void
     */
    protected function registerCore()
    {
        $this->app->singleton(LittleJWT::class, function ($app) {
            $jwk = $app->make(KeyBuildable::class)->build();

            return new LittleJWT($app, $jwk);
        });

        $this->app->bind(Builder::class, function () {
            return new Builder(Build::jws());
        });

        $this->app->alias(LittleJWT::class, 'littlejwt');
        $this->app->alias(Builder::class, 'littlejwt.builder');
        //$this->app->alias(Validator::class, 'littlejwt.verifier');
    }

    /**
     * Registers the classes used by the Jose library.
     *
     * @return void
     */
    protected function registerJoseLibrary()
    {
        $this->app->singleton(AlgorithmManager::class, function ($app) {
            $algorithm = $app->make('littlejwt.algorithm');
            $algorithms = ! is_null($algorithm) ? [$algorithm] : [];

            return new AlgorithmManager($algorithms);
        });

        $this->app->singleton('littlejwt.algorithm', function ($app) {
            $algorithm = config('littlejwt.algorithm');

            return ! is_null($algorithm) ? $app->make($algorithm) : null;
        });

        $this->app->singleton(JWSBuilder::class, function ($app) {
            $algorithmManager = $app->make(AlgorithmManager::class);

            return new JWSBuilder($algorithmManager);
        });
    }

    /**
     * Registers the blacklist manager
     *
     * @return void
     */
    protected function registerBlacklistManager()
    {
        $this->app->singleton(BlacklistManager::class, function ($app) {
            return new BlacklistManager($app);
        });
    }

    /**
     * Registers the factories
     *
     * @return void
     */
    protected function registerFactories()
    {
        $this->app->singleton(JWTBuilder::class, function () {
            return new JWTBuilder();
        });

        $this->app->singleton(KeyBuildable::class, function ($app) {
            $config = $app->config->get('littlejwt.key', []);

            return new KeyBuilder($app, $config);
        });

        $this->app->singleton(JWTHasher::class, function ($app) {
            return new JWTHasher($app);
        });
    }

    /**
     * Registers the JWT builders.
     *
     * @return void
     */
    protected function registerBuilders()
    {
        $this->app->singleton(Builders\DefaultBuilder::class, function ($app) {
            $config = config('littlejwt', []);

            return new Builders\DefaultBuilder($app, $config);
        });

        $this->app->singleton(Builders\GuardBuilder::class, function ($app) {
            $config = config('littlejwt', []);

            return new Builders\GuardBuilder($app, $config);
        });

        $this->app->alias(Builders\DefaultBuilder::class, 'littlejwt.builders.default');
        $this->app->alias(Builders\GuardBuilder::class, 'littlejwt.builders.guard');
    }

    /**
     * Registers the JWT validators.
     *
     * @return void
     */
    protected function registerValidators()
    {
        $this->app->singleton(Validators\DefaultValidator::class, function ($app) {
            $config = config('littlejwt', []);

            return new Validators\DefaultValidator($app, $config);
        });

        $this->app->singleton(Validators\GuardValidator::class, function ($app) {
            $config = config('littlejwt', []);

            return new Validators\GuardValidator($app, $config);
        });

        $this->app->alias(Validators\GuardValidator::class, 'littlejwt.validators.guard');
    }

    /**
     * Registers the adapters available to use with the guard.
     *
     * @return void
     */
    protected function registerGuardAdapters()
    {
        $this->app->bind(Adapters\GenericAdapter::class, function ($app) {
            $config = $this->getAdapterConfig('generic');
            $jwt = $app[LittleJWT::class];

            return new Adapters\GenericAdapter($app, $jwt, $config);
        });

        $this->app->bind(Adapters\FingerprintAdapter::class, function ($app) {
            $config = $this->getAdapterConfig('fingerprint');
            $jwt = $app[LittleJWT::class];
            $generic = $app[Adapters\GenericAdapter::class];

            return new Adapters\FingerprintAdapter($app, $jwt, $generic, $config);
        });
    }

    /**
     * Gets the configuration for an adapter.
     *
     * @param string $adapter Name of adapter
     * @return array
     */
    protected function getAdapterConfig(string $adapter)
    {
        return $this->app['config']["auth.guards.jwt.adapters.{$adapter}"];
    }

    /**
     * Boots the Little JWT auth guard.
     *
     * @return void
     */
    protected function bootGuard()
    {
        Auth::extend('littlejwt', function ($app, $name, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\Guard...
            $config = array_merge([
                'input_key' => 'token',
                'model' => false,
                'adapter' => 'generic',
            ], $config);

            $provider = Auth::createUserProvider($config['provider'] ?? null);

            $adapterConfig = $config['adapters'][$config['adapter']];
            $adapter = $app->make($adapterConfig['adapter']);

            $guard = new Guard($app, $adapter, $provider, $app['request'], $config);

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    /**
     * Boots any macros
     *
     * @return void
     */
    protected function bootMacros()
    {
        $littleJwt = $this->app->make(LittleJWT::class);

        Request::macro('getToken', function ($inputKey = 'token') {
            $token = $this->query($inputKey);

            if (empty($token)) {
                $token = $this->input($inputKey);
            }

            if (empty($token)) {
                $token = $this->bearerToken();
            }

            if (empty($token)) {
                $token = $this->getPassword();
            }

            return $token;
        });

        Request::macro('getJwt', function ($inputKey = 'token') use ($littleJwt) {
            $token = $this->getToken($inputKey);

            return ! is_null($token) ? $littleJwt->parseToken($token) : null;
        });

        ResponseFactory::macro('withJwt', function (JWT $jwt) {
            return ResponseFactory::json(ResponseBuilder::buildFromJwt($jwt));
        });

        Response::macro('attachJwt', function ($jwt) {
            $this->header('Authorization', sprintf('Bearer %s', (string) $jwt));

            return $this;
        });
    }
}
