<?php

namespace LittleApps\LittleJWT;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFactory;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Commands\GenerateSecretCommand;
use LittleApps\LittleJWT\Contracts\Keyable;
use LittleApps\LittleJWT\Factories\ClaimManagerBuilder;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Factories\JWTHasher;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\Factories\OpenSSLBuilder;
use LittleApps\LittleJWT\Factories\ValidatableBuilder;
use LittleApps\LittleJWT\Guards\Adapters;
use LittleApps\LittleJWT\Guards\Guard;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Laravel\Middleware\ValidToken as ValidTokenMiddleware;
use LittleApps\LittleJWT\Laravel\Rules\ValidToken as ValidTokenRule;
use LittleApps\LittleJWT\Utils\ResponseBuilder;
use LittleApps\LittleJWT\Validation\Validatables\StackValidatable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('littlejwt')
            ->hasMigration('create_jwt_blacklist_table')
            ->hasCommand(GenerateSecretCommand::class);

        if (! $this->app->runningUnitTests()) {
            $package->hasConfigFile();
        } else {
            $this->mergeConfigFrom($package->basePath('/../config/littlejwt.testing.php'), 'littlejwt');
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function packageRegistered()
    {
        $this->registerCore();
        $this->registerFactories();
        $this->registerBlacklistManager();
        $this->registerBuildables();
        $this->registerValidatables();
        $this->registerGuardAdapters();
        $this->registerMiddleware();
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
        $this->bootValidatorRules();
    }

    /**
     * Registers the core Little JWT classes.
     *
     * @return void
     */
    protected function registerCore()
    {
        $this->app->singleton(LittleJWT::class, function ($app) {
            $jwk = $app->make(Keyable::class)->build();

            return new LittleJWT($app, $jwk);
        });

        $this->app->alias(LittleJWT::class, 'littlejwt');
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
        $this->app->bind(ClaimManagerBuilder::class, function ($app) {
            $config = $app->config->get('littlejwt.builder.mutators', ['header' => [], 'payload' => []]);

            return new ClaimManagerBuilder($config);
        });

        $this->app->singleton(JWTBuilder::class, function ($app) {
            $claimManagerBuilder = $app->make(ClaimManagerBuilder::class);

            return new JWTBuilder($claimManagerBuilder);
        });

        $this->app->singleton(Keyable::class, function ($app) {
            $config = $app->config->get('littlejwt.key', []);

            return new KeyBuilder($app, $config);
        });

        $this->app->singleton(JWTHasher::class, function ($app) {
            $algorithm = $app->config->get('littlejwt.key.algorithm');

            return new JWTHasher($app->make($algorithm));
        });

        $this->app->singleton(OpenSSLBuilder::class, function ($app) {
            $config = $app->config->get('littlejwt.openssl', []);

            return new OpenSSLBuilder($config);
        });
    }

    /**
     * Registers the JWT buildables.
     *
     * @return void
     */
    protected function registerBuildables()
    {
        $buildables = $this->app->config->get('littlejwt.buildables', []);

        foreach ($buildables as $key => $options) {
            if (! isset($options['buildable'])) {
                continue;
            }

            $buildable = $options['buildable'];

            $config = array_diff_key($options, array_flip(['buildable']));

            $this->app->singleton("littlejwt.buildables.{$key}", function ($app) use ($buildable, $config) {
                return $app->make($buildable, ['config' => $config]);
            });
        }
    }

    /**
     * Registers the JWT validators.
     *
     * @return void
     */
    protected function registerValidatables()
    {
        $validatables = $this->app->config->get('littlejwt.validatables', []);

        foreach ((array) $validatables as $key => $options) {
            if (! isset($options['validatable'])) {
                continue;
            }

            $validatable = $options['validatable'];

            $config = array_diff_key($options, array_flip(['validatable']));

            $this->app->singleton("littlejwt.validatables.{$key}", function ($app) use ($validatable, $config) {
                return $app->make($validatable, ['config' => $config]);
            });
        }
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
     * Registers the middleware.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware('validtoken', ValidTokenMiddleware::class);
    }

    /**
     * Gets the configuration for an adapter.
     *
     * @param string $adapter Name of adapter
     * @return array
     */
    protected function getAdapterConfig(string $adapter)
    {
        return $this->app['config']["littlejwt.guard.adapters.{$adapter}"];
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
                'adapter' => 'generic',
            ], $config);

            $provider = Auth::createUserProvider($config['provider'] ?? null);

            $adapterConfig = $this->getAdapterConfig($config['adapter']);
            $adapter = $app->makeWith($adapterConfig['adapter'], ['config' => $adapterConfig]);

            $guard = new Guard($app, $adapter, $provider, $app['request'], $config);

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    protected function bootValidatorRules()
    {
        $this->app['validator']->extendImplicit('validtoken', function ($attribute, $value, $parameters, $validator) {
            if (! empty($parameters)) {
                $stack = array_map(function ($key) {
                    return is_string($key) ? ValidatableBuilder::resolve($key) : $key;
                }, (array) $parameters);

                $validatable = new StackValidatable($stack);

                $rule = new ValidTokenRule([$validatable, 'validate'], false);
            } else {
                $rule = new ValidTokenRule();
            }

            return $rule->passes($attribute, $value);
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
