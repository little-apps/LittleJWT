<?php

namespace LittleApps\LittleJWT\JWT;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use LittleApps\LittleJWT\Utils\Base64Encoder;
use LittleApps\LittleJWT\Utils\JsonEncoder;
use RuntimeException;

class ClaimManager implements Countable, Jsonable, Arrayable, ArrayAccess
{
    /**
     * Part claim manager is for
     *
     * @var string One of ClaimManagerBuilder::PART_* constants
     */
    protected $part;

    /**
     * Claims
     *
     * @var \Illuminate\Support\Collection
     */
    protected $claims;

    public function __construct(string $part, array $claims)
    {
        $this->part = $part;
        $this->claims = collect($claims);
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
    #[\ReturnTypeWillChange]
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
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->claims->all();
    }

    /**
     * Gets the claims JSON encoded.
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        // TODO: Pass JSON options
        return JsonEncoder::encode($this->toArray());
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
