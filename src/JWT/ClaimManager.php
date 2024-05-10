<?php

namespace LittleApps\LittleJWT\JWT;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use LittleApps\LittleJWT\Build\ClaimBuildOptions;
use LittleApps\LittleJWT\Utils\Base64Encoder;
use LittleApps\LittleJWT\Utils\JsonEncoder;
use RuntimeException;

class ClaimManager implements Countable, Jsonable, Arrayable, ArrayAccess
{
    public const PART_HEADER = 'header';

    public const PART_PAYLOAD = 'payload';

    /**
     * Part claim manager is for
     *
     * @var string One of ClaimManager::PART_* constants
     */
    protected $part;

    /**
     * Claims
     *
     * @var \Illuminate\Support\Collection<string, ClaimBuildOptions>
     */
    protected $claims;

    public function __construct(string $part, $claims)
    {
        $this->part = $part;
        $this->claims = $this->mapToClaimBuildOptions(collect($claims));
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
        if (is_null($key)) {
            return collect($this->claims);
        }

        $claim = $this->getClaim($key);

        return ! is_null($claim) ? $claim->getValue() : $default;
    }

    /**
     * Gets a claim for key.
     *
     * @param string $key Claim key.
     * @param mixed $default Returned if claim key doesn't exist. (default: null)
     * @return ClaimBuildOptions|mixed
     */
    public function getClaim(string $key, $default = null)
    {
        return $this->claims->get($key, $default);
    }

    /**
     * Sets a claim value
     *
     * @param string $key
     * @param mixed $value
     * @return ClaimBuildOptions
     */
    public function set(string $key, $value)
    {
        $claimBuildOptions = new ClaimBuildOptions($this->part, $key, $value);

        $this->claims[$key] = $claimBuildOptions;

        return $claimBuildOptions;
    }

    /**
     * Unsets a claim
     *
     * @param string $key
     * @return static
     */
    public function unset(string $key): static
    {
        unset($this->claims[$key]);

        return $this;
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
     * Maps claims to ClaimBuildOptions
     *
     * @param mixed $claims
     * @return Collection<string, ClaimBuildOptions>
     */
    protected function mapToClaimBuildOptions($claims)
    {
        return collect($claims)->map(
            fn ($value, $key) => $value instanceof ClaimBuildOptions ? $value : new ClaimBuildOptions($this->part, $key, $value)
        );
    }

    /**
     * Maps ClaimBuildOptions to values.
     *
     * @return Collection
     */
    public function mapToValues()
    {
        return $this->claims->map(fn ($options) => $options->getValue());
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
        $this->set($offset, $value);
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
        $this->unset($offset);
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
        return JsonEncoder::encode($this->mapToValues()->toArray());
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

    /**
     * Merges multiple ClaimManager instances together.
     * If multiple claim managers have the same key, the latter value is used.
     *
     * @param string $part Part claims are for
     * @param ClaimManager ...$claimManagers Claim managers to merge
     * @return self
     */
    public static function merge(string $part, self ...$claimManagers)
    {
        $claims = [];

        foreach ($claimManagers as $claimManager) {
            $claims = array_merge($claims, $claimManager->toArray());
        }

        return new self($part, $claims);
    }
}
