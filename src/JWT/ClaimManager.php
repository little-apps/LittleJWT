<?php

namespace LittleApps\LittleJWT\JWT;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use LittleApps\LittleJWT\Utils\Base64Encoder;
use LittleApps\LittleJWT\Utils\JsonEncoder;
use RuntimeException;

class ClaimManager implements Countable, Jsonable, Arrayable, ArrayAccess
{
    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    /**
     * Mutator manager
     *
     * @var MutatorManager
     */
    protected $mutatorManager;

    /**
     * Claims
     *
     * @var \Illuminate\Support\Collection
     */
    protected $claims;

    public function __construct(Application $app, MutatorManager $mutatorManager, array $claims)
    {
        $this->app = $app;
        $this->mutatorManager = $mutatorManager;

        $this->claims = collect($claims);
    }

    /**
     * Unserialize claims
     *
     * @return $this
     */
    public function unserialized()
    {
        $claims = $this->claims->all();

        $this->claims->transform(fn ($value, $key) => $this->mutatorManager->unserialize($key, $value, $claims));

        return $this;
    }

    /**
     * Checks if claim with key exists.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->claims[$key]);
    }

    /**
     * Gets a claim value.
     *
     * @param string|null $key The claim key or if null, all the claims. (default: null)
     * @param mixed $default Default value if claim key doesn't exist. (default: null)
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return ! is_null($key) ? $this->claims->get($key, $default) : collect($this->claims);
    }

    /**
     * Gets the number of claims.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->claims->count();
    }

    /**
     * Allows for claims to be checked using isset() function.
     *
     * @param string $name Claim key
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Allows for claims to be retrieved as properties.
     *
     * @param string $name Claim key
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Check an offset exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Allows for claims to be retrieved as an array key.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Throws an RuntimeException since ClaimManager is immutable.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     * @throws RuntimeException
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Attempt to mutate immutable ' . static::class . ' object.');
    }

    /**
     * Throws an RuntimeException since ClaimManager is immutable.
     *
     * @param string $offset
     * @return void
     * @throws RuntimeException
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Attempt to mutate immutable ' . static::class . ' object.');
    }

    /**
     * Gets the claims as key/value array with values serialized.
     *
     * @return array
     */
    public function toSerialized()
    {
        $claims = $this->claims->all();

        return $this->claims->map(function ($value, $key) use ($claims) {
            return $this->mutatorManager->serialize($key, $value, $claims);
        })->all();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->claims->toArray();
    }

    /**
     * Gets the claims JSON encoded.
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        $serialized = $this->toSerialized();

        return JsonEncoder::encode($serialized);
    }

    /**
     * Gets the claims base 64 and JSON encoded.
     *
     * @return string
     */
    public function __toString()
    {
        $json = $this->toJson();

        return Base64Encoder::encode($json);
    }
}
