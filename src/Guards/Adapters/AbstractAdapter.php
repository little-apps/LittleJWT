<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Container\Container;
use LittleApps\LittleJWT\Contracts\GuardAdapter;

use LittleApps\LittleJWT\Contracts\Verifiable;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\LittleJWT;

abstract class AbstractAdapter implements GuardAdapter
{
    /**
     * Application container
     *
     * @var Container
     */
    protected $container;

    /**
     * The instance for building and validating JWTs
     *
     * @var LittleJWT
     */
    protected $jwt;

    /**
     * The options to use for the adapter.
     *
     * @var array
     */
    protected $config;

    public function __construct(Container $container, LittleJWT $jwt, array $config)
    {
        $this->container = $container;
        $this->jwt = $jwt;

        $this->config = $config;
    }

    /**
     * Parse a token from a string to a JWT.
     * This does NOT check if the JWT is valid.
     *
     * @param string $token
     * @return JWT JWT instance or null if unable to be parsed.
     */
    public function parseToken(string $token)
    {
        return $this->jwt->parseToken($token);
    }

    /**
     * Runs JWT through Validator.
     *
     * @param JWT $jwt
     * @return bool True if JWT is validated.
     */
    public function validateJwt(JWT $jwt)
    {
        $validatable = $this->buildValidatable();

        return $this->jwt->validateJWT($jwt, [$validatable, 'validate']);
    }

    /**
     * Gets a user from the JWT
     *
     * @param UserProvider $provider
     * @param JWT $jwt
     * @return Authenticatable
     */
    public function getUserFromJwt(UserProvider $provider, JWT $jwt)
    {
        return $provider->retrieveById($jwt->getPayload()->sub);
    }

    /**
     * Builds the Validatable used to validate a JWT.
     *
     * @abstract
     * @return \LittleApps\LittleJWT\Contracts\Validatable
     */
    abstract protected function buildValidatable();
}
