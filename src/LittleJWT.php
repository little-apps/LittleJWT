<?php

namespace LittleApps\LittleJWT;

use Illuminate\Contracts\Foundation\Application;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Build\Build;
use LittleApps\LittleJWT\Build\Buildables\StackBuildable;
use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\Factories\ValidatableBuilder;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Validation\Valid;
use LittleApps\LittleJWT\Validation\Validatables\StackValidatable;

/**
 * This class is responsible for generating and validating JSON Web Tokens.
 * @author Nick H <nick@little-apps.com>
 * @license https://github.com/little-apps/LittleJWT/blob/main/LICENSE.md
 * @see https://www.getlittlejwt.com
 */
class LittleJWT
{
    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    /**
     * The JWK to use for building and validating JWTs
     *
     * @var JWK
     */
    protected $jwk;

    public function __construct(Application $app, JWK $jwk)
    {
        $this->app = $app;
        $this->jwk = $jwk;
    }

    /**
     * Creates a signed JWT
     *
     * @param callable $callback Callback that receives LittleApps\LittleJWT\Builder instance.
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return string
     */
    public function createToken(callable $callback = null, $applyDefault = true)
    {
        return (string) $this->createJWT($callback, $applyDefault);
    }

    /**
     * Creates a signed JWT instance.
     *
     * @param callable $callback Callback that receives LittleApps\LittleJWT\Builder instance.
     * @param bool $applyDefault If true, the default claims are applied to the JWT. (default is true)
     * @return JWT
     */
    public function createJWT(callable $callback = null, $applyDefault = true)
    {
        $build = $this->buildJWT();

        $callbacks = [];

        if ($applyDefault) {
            array_push($callbacks, $this->getDefaultBuildableCallback());
        }

        if (is_callable($callback)) {
            array_push($callbacks, $callback);
        }

        $buildable = new StackBuildable($callbacks);

        return $build->passBuilderThru($buildable)->build();
    }

    /**
     * Creates a Build instance to create a JWT.
     *
     * @return Build
     */
    public function buildJWT()
    {
        $build = new Build($this->app, $this->jwk);

        return $build;
    }

    /**
     * Builds a JWT instance from a string.
     * This does NOT check that the JWT is valid.
     *
     * @param string $token
     * @return \LittleApps\LittleJWT\JWT\JWT|null Returns JWT or null if token cannot be parsed.
     */
    public function parseToken(string $token)
    {
        try {
            $builder = $this->app->make(JWTBuilder::class);

            $headerMutators = $this->app->config->get('littlejwt.builder.mutators.header', []);
            $payloadMutators = $this->app->config->get('littlejwt.builder.mutators.payload', []);

            return $builder->buildFromExisting($token, $headerMutators, $payloadMutators);
        } catch (CantParseJWTException $ex) {
            return null;
        }
    }

    /**
     * Creates a Valid instance for validating a JWT.
     *
     * @param JWT $jwt JWT instance to validate (generated by parseToken() method)
     * @return Valid Valid instance (before validation is done)
     */
    public function validJWT(JWT $jwt)
    {
        $valid = new Valid($this->app, $jwt, $this->jwk);

        return $valid;
    }

    /**
     * Validates a JSON Web Token (JWT).
     *
     * @param JWT $jwt JWT instance to validate (generated by parseToken() method)
     * @param callable $callback Callable that receives Validator to set rules for JWT.
     * @param bool $applyDefault If true, the default validatable is used first. (default: true)
     * @return bool True if token is valid.
     */
    public function validateJWT(JWT $jwt, callable $callback = null, $applyDefault = true)
    {
        if ($applyDefault) {
            $callbacks = [$this->getDefaultValidatableCallback()];

            if (is_callable($callback)) {
                array_push($callbacks, $callback);
            }

            $validatable = new StackValidatable($callbacks);

            $passthrough = [$validatable, 'validate'];
        } else {
            // No need to create a StackValidatable instance for just 1 validatable
            $passthrough = $callback;
        }

        $valid = $this->validJWT($jwt);

        if (is_callable($passthrough)) {
            $valid->passValidatorThru($passthrough);
        }

        // Run the JWT through a Valid instance and return the result.
        return $valid->passes();
    }

    /**
     * Parses a token as a JSON Web Token (JWT) and validates it.
     *
     * @param string $token The token to parse as a JWT and validate.
     * @param callable $callback Callable that receives Validator to set rules for JWT.
     * @param bool $applyDefault If true, the default validatable is used first. (default: true)
     * @return bool True if token is valid.
     */
    public function validateToken(string $token, $callback = null, $applyDefault = true)
    {
        $jwt = $this->parseToken($token);

        return ! is_null($jwt) ? $this->validateJWT($jwt, $callback, $applyDefault) : false;
    }

    /**
     * Gets the default buildable callback.
     *
     * @return callable
     */
    protected function getDefaultBuildableCallback()
    {
        $alias = sprintf('littlejwt.buildables.%s', $this->app->config->get('littlejwt.defaults.buildable'));

        $buildable = $this->app->make($alias);

        return [$buildable, 'build'];
    }

    /**
     * Gets the default Validatable callback.
     *
     * @return callable
     */
    protected function getDefaultValidatableCallback()
    {
        $validatable = ValidatableBuilder::resolve($this->app->config->get('littlejwt.defaults.validatable'));

        return [$validatable, 'validate'];
    }
}
