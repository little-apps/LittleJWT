<?php

namespace LittleApps\LittleJWT\JWT;

use Countable;

use Illuminate\Contracts\Support\Jsonable;

use LittleApps\LittleJWT\JWT\Concerns\MutatesClaims;
use LittleApps\LittleJWT\Utils\Base64Encoder;
use LittleApps\LittleJWT\Utils\JsonEncoder;

class ClaimManager implements Countable, Jsonable
{
    use MutatesClaims;

    protected $claims;

    public function __construct(array $claims, array $mutators = [])
    {
        $this->claims = collect($claims)->map(function ($value, $key) {
            return is_object($value) ? $value : $this->unserialize($key, $value);
        });

        $this->mutators = $mutators;
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
     * @param string|null $key The claim key or if null, all the claim values. (default: null)
     * @return mixed
     */
    public function get($key = null)
    {
        return ! is_null($key) ? $this->claims->get($key) : collect($this->claims);
    }

    /**
     * Gets the number of claims.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
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
     * Gets the claims as key/value array with values serialized.
     *
     * @return array
     */
    public function toSerialized()
    {
        return $this->claims->map(function ($value, $key) {
            return $this->serialize($key, $value);
        })->all();
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
