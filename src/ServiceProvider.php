<?php

namespace LittleApps\LittleJWT;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFactory;
use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Factories\KeyBuilder;
use LittleApps\LittleJWT\Factories\LittleJWTBuilder;
use LittleApps\LittleJWT\Factories\OpenSSLBuilder;
use LittleApps\LittleJWT\Factories\ValidatableBuilder;
use LittleApps\LittleJWT\Guards\Adapters;
use LittleApps\LittleJWT\Guards\Guard;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWK\JWKValidator;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\Laravel\Middleware\ValidToken as ValidTokenMiddleware;
use LittleApps\LittleJWT\Laravel\Rules\ValidToken as ValidTokenRule;
use LittleApps\LittleJWT\Utils\ResponseBuilder;
use LittleApps\LittleJWT\Validation\Validatables\StackValidatable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('littlejwt')
            ->hasMigration('create_jwt_blacklist_table')
            ->hasCommands(
                Commands\GeneratePhraseCommand::class,
                Commands\GenerateP12Command::class,
                Commands\GeneratePemCommand::class,
                Commands\BlacklistPurgeCommand::class
            );

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
        $this->bootJwkValidator();
    }

    /**
     * Registers the core Little JWT classes.
     *
     * @return void
     */
    protected function registerCore()
    {
        $this->app->singleton(LittleJWT::class, function (Container $app) {
            $builder = new LittleJWTBuilder($app->make(JsonWebKey::class));

            return $builder->withJwkValidator(JWKValidator::default())->build();
        });

        $this->app->bind(JsonWebKey::class, function (Container $app) {
            $config = $app->config->get('littlejwt.key', []);
            $jwk = KeyBuilder::buildFromConfig($config);

            return $jwk;
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

            return new Adapters\GenericAdapter($app, $config);
        });

        $this->app->bind(Adapters\FingerprintAdapter::class, function ($app) {
            $config = $this->getAdapterConfig('fingerprint');
            $generic = $app[Adapters\GenericAdapter::class];

            return new Adapters\FingerprintAdapter($app, $generic, $config);
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
     * @param  string  $adapter  Name of adapter
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

                $rule = new ValidTokenRule($validatable, false);
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
        $handler = $this->app->make(LittleJWT::class)->handler();

        /**
         * Get the token for the request.
         *
         * @param  Request  $request  Request to get token from
         * @param  string  $inputKey  Name of input to get token from (if it exists).
         * @return string|null
         */
        Request::macro('getToken', function ($inputKey = 'token') {
            /*
             * This exact same functionality exists in the getTokenForRequest method in a \Illuminate\Auth\TokenGuard instance.
             * However, it's not guaranteed that the token guard will be set and it doesn't make sense to instantiate it just to use 1 method.
             */
            $tokens = [
                $this->query($inputKey),
                $this->input($inputKey),
                $this->bearerToken(),
                $this->getPassword(),
            ];

            foreach ($tokens as $token) {
                if (! empty($token)) {
                    return $token;
                }
            }

            return null;
        });

        Request::macro('getJwt', function ($inputKey = 'token') use ($handler) {
            $token = $this->getToken($inputKey);

            return ! is_null($token) ? $handler->parse($token) : null;
        });

        ResponseFactory::macro('withJwt', function (JsonWebToken $jwt) {
            return ResponseFactory::json(ResponseBuilder::buildFromJwt($jwt));
        });

        $attachJwtCallback = function ($jwt) {
            return $this->header('Authorization', sprintf('Bearer %s', (string) $jwt));
        };

        Response::macro('attachJwt', $attachJwtCallback);
        JsonResponse::macro('attachJwt', $attachJwtCallback);
        RedirectResponse::macro('attachJwt', $attachJwtCallback);
    }

    /**
     * Boots JWKValidator
     *
     * @return void
     */
    protected function bootJwkValidator() {
        JWKValidator::defaults(fn () => (new JWKValidator())->withFallback(fn () => KeyBuilder::generateRandomJwk(1024, [
            'alg' => 'HS256',
            'use' => 'sig'
        ])));
    }
}
