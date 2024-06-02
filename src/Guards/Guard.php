<?php

namespace LittleApps\LittleJWT\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use LittleApps\LittleJWT\Contracts\GuardAdapter;

class Guard implements GuardContract
{
    use ForwardsCalls, GuardHelpers, Macroable {
        __call as macroCall;
    }

    /**
     * Application container
     */
    protected readonly Container $container;

    /**
     * The guard adapter to use.
     *
     * @var GuardAdapter
     */
    protected $adapter;

    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The options to use for the guard
     */
    protected readonly array $config;

    public function __construct(Container $container, GuardAdapter $adapter, UserProvider $provider, Request $request, array $config)
    {
        $this->container = $container;

        $this->setAdapter($adapter);
        $this->setRequest($request);
        $this->setProvider($provider);

        $this->config = $config;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user()
    {
        if ($this->hasUser()) {
            return $this->user;
        }

        // If user is not set, try to retrieve it from JWT.
        // TODO: Allow input key to be specified in configuration.
        $token = $this->request->getToken();

        if (! empty($token)) {
            $user = $this->getUserFromToken($token);

            if (! is_null($user)) {
                $this->setUser($user);
            }
        }

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (is_null($user) || ! $this->provider->validateCredentials($user, $credentials)) {
            return false;
        }

        $this->user = $user;

        return true;
    }

    /**
     * Gets the guard adapter.
     *
     * @return GuardAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Gets the config for the guard.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Gets the current request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets the guard adapter.
     *
     * @return $this
     */
    public function setAdapter(GuardAdapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Set the current request instance.
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        if (method_exists($this->getAdapter(), 'setRequest')) {
            $this->getAdapter()->setRequest($request);
        }

        return $this;
    }

    /**
     * Gets the user from a token.
     *
     * @param  string  $token  Token that is a JWT.
     * @return Authenticatable|null User (if found) or null (if not found)
     */
    public function getUserFromToken(string $token)
    {
        // First check if token is actually a JWT
        $jwt = $this->getAdapter()->parse($token);

        if (! is_null($jwt) && $this->getAdapter()->validate($jwt)) {
            return $this->getAdapter()->getUserFromJwt($this->provider, $jwt);
        }

        return null;
    }

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->forwardCallTo($this->getAdapter(), $method, $parameters);
    }
}
