<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use LittleApps\LittleJWT\LittleJWT;
use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Contracts\Verifiable;
use LittleApps\LittleJWT\Contracts\GuardAdapter;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Container\Container;

abstract class AbstractAdapter implements GuardAdapter {
    /**
     * Application container
     *
     * @var Container
     */
    protected $container;

    /**
     * The instance for building and verifying JWTs
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

    public function __construct(Container $container, LittleJWT $jwt, array $config) {
        $this->container = $container;
        $this->jwt = $jwt;

        $this->config = $config;
    }

    /**
     * Parse a token from a string to a JWT.
     * This does NOT verify if the JWT is valid.
     *
     * @param string $token
     * @return JWT JWT instance or null if unable to be parsed.
     */
    public function parseToken(string $token) {
        return $this->jwt->parseToken($token);
    }

    /**
     * Runs JWT through verifier.
     *
     * @param JWT $jwt
     * @param Verifiable $verifier
     * @return bool True if JWT is verified.
     */
    public function verifyJwt(JWT $jwt) {
        $verifier = $this->buildVerifier();

        return $this->jwt->verifiedJWT($jwt, [$verifier, 'verify']);
    }

    /**
     * Gets a user from the JWT
     *
     * @param UserProvider $provider
     * @param JWT $jwt
     * @return Authenticatable
     */
    public function getUserFromJwt(UserProvider $provider, JWT $jwt) {
        return $provider->retrieveById($jwt->getPayload()->sub);
    }

    /**
     * Builds the verifier used to verify a JWT and retrieve a user.
     *
     * @abstract
     * @return \LittleApps\LittleJWT\Contracts\Verifiable
     */
    abstract protected function buildVerifier();


}
