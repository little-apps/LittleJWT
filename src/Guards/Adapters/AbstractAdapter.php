<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Container\Container;
use LittleApps\LittleJWT\Contracts\GuardAdapter;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\LittleJWT;

abstract class AbstractAdapter implements GuardAdapter
{
    /**
     * Application container
     */
    protected readonly Container $container;

    /**
     * The options to use for the adapter.
     */
    protected readonly array $config;

    public function __construct(Container $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * Parse a token from a string to a JWT.
     * This does NOT check if the JWT is valid.
     *
     * @return JsonWebToken|null JWT instance or null if unable to be parsed.
     */
    public function parse(string $token)
    {
        return $this->getHandler()->parse($token);
    }

    /**
     * Runs JWT through Validator.
     *
     * @return bool True if JWT is validated.
     */
    public function validate(JsonWebToken $jwt)
    {
        $callback = $this->getValidatorCallback();

        return $this->getHandler()->validate($jwt, $callback)->passes();
    }

    /**
     * Gets a user from the JWT
     *
     * @return Authenticatable
     */
    public function getUserFromJwt(UserProvider $provider, JsonWebToken $jwt)
    {
        return $provider->retrieveById($jwt->getPayload()->sub);
    }

    /**
     * Gets the LittleJWT handler
     *
     * @return \LittleApps\LittleJWT\Core\Handler
     */
    protected function getHandler()
    {
        return $this->container->make(LittleJWT::class)->handler();
    }

    /**
     * Gets a callback that receives a Validator to specify the JWT validations.
     *
     * @return callable
     */
    abstract protected function getValidatorCallback();
}
