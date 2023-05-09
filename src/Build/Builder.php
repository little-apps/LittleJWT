<?php

namespace LittleApps\LittleJWT\Build;

use BadMethodCallException;

use Illuminate\Support\Traits\Macroable;

class Builder
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Header claims
     *
     * @var \Illuminate\Support\Collection
     */
    protected $headers;

    /**
     * Payload claims.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $payload;

    /**
     * Claim keys that belong in header.
     *
     * @var array
     */
    protected $headerClaims;

    /**
     * Claim keys that belong in payload.
     *
     * @var array
     */
    protected $payloadClaims;

    /**
     * Initializes Builder instance.
     *
     * @param array $headerClaims Claim keys that go in header.
     * @param array $payloadClaims Claim keys that go in payload.
     */
    public function __construct(array $headerClaims = [], array $payloadClaims = [])
    {
        $this->headerClaims = $headerClaims;
        $this->payloadClaims = $payloadClaims;

        $this->headers = collect();
        $this->payload = collect();
    }

    /**
     * Adds a claim to either the header or payload.
     *
     * @param string $key Claim key
     * @param mixed $value Claim value
     * @return $this
     */
    public function addClaim($key, $value)
    {
        $inHeader = ($this->isHeaderClaim($key) && ! $this->isPayloadClaim($key));

        return $inHeader ? $this->addHeaderClaim($key, $value) : $this->addPayloadClaim($key, $value);
    }

    /**
     * Adds a claim to the header.
     *
     * @param string $key Claim key
     * @param mixed $value Claim value. Will be sent through ClaimsSerializer for serialization.
     * @return ClaimBuildOptions
     */
    public function addHeaderClaim($key, $value)
    {
        return $this->headers[$key] = new ClaimBuildOptions($this, ClaimBuildOptions::PART_HEADERS, $key, $value);
    }

    /**
     * Adds a claim to the payload.
     *
     * @param string $key Claim key
     * @param mixed $value Claim value. Will be sent through ClaimsSerializer for serialization.
     * @return ClaimBuildOptions
     */
    public function addPayloadClaim($key, $value)
    {
        return $this->payload[$key] = new ClaimBuildOptions($this, ClaimBuildOptions::PART_PAYLOAD, $key, $value);
    }

    /**
     * Checks if claim with key exists in the header.
     *
     * @param string $key Claim key
     * @return bool True if claim with key exists in header.
     */
    public function hasHeaderClaim($key)
    {
        return isset($this->headers[$key]);
    }

    /**
     * Checks if claim with key exists in the payload.
     *
     * @param string $key Claim key
     * @return bool True if claim with key exists in payload.
     */
    public function hasPayloadClaim($key)
    {
        return isset($this->payload[$key]);
    }

    /**
     * Removes a claim with key from header or payload.
     *
     * @param string $key Claim key
     * @param bool $inHeader If true, removes claim from header. Otherwise, removes claim from payload. (default: false)
     * @return $this
     */
    public function remove($key, $inHeader = false)
    {
        if ($inHeader) {
            unset($this->headers[$key]);
        } else {
            unset($this->payload[$key]);
        }

        return $this;
    }

    /**
     * Gets the header claims.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->map(fn ($options) => $options->getValue())->all();
    }

    /**
     * Gets the headers claim options.
     *
     * @return list<ClaimBuildOptions>
     */
    public function getHeadersOptions() {
        return $this->headers->all();
    }

    /**
     * Gets the payload claims.
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload->map(fn ($options) => $options->getValue())->all();
    }

    /**
     * Gets the payload claim options.
     *
     * @return list<ClaimBuildOptions>
     */
    public function getPayloadOptions() {
        return $this->payload->all();
    }

    /**
     * Gets the header and payload claims as one array.
     * This is not recommended if a claim key exists in both the header and payload claims.
     *
     * @return array
     */
    public function all()
    {
        return array_merge($this->getHeaders(), $this->getPayload());
    }

    /**
     * Allows for checking if a claim exists in the payload using isset() on instance property.
     * Note: Use has() method to check for claim in header.
     * Example:
     *   $exists = isset($builder->iat); // Checks if 'iat' claim exists in payload.
     *
     * @param string $key Claim key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Allows for existing claim to be removed from payload using unset() on instance property.
     * Note: Use remove() method to remove claims in header.
     * Example:
     *   unset($builder->iat); // Removes iat claim from payload.
     *
     * @param string $key Claim key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * Allows for claims to be added by setting it as a property for the Builder instance.
     * All claims set as a property are automatically added to payload claims.
     * Example:
     *   $builder->iat = now(); // Adds 'iat' claim with current date/time as value to payload.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->addClaim($key, $value);
    }

    /**
     * Allows for claims to be retrieved as properties.
     *
     * @param string $key Claim key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->payload[$key];
    }

    /**
     * Allows for claims to be added using the claim key as the method name and claim value as the method parameter.
     * Example:
     *   $builder->iat(now()); // Adds 'iat' claim with current date/time as value to payload.
     *   $builder->typ('JWT', true); // Adds 'typ' claim with value 'JWT' to header.
     *
     * @param string $name
     * @param array $parameters
     * @return $this
     */
    public function __call($name, $parameters)
    {
        if (static::hasMacro($name)) {
            return $this->macroCall($name, $parameters);
        }

        $paramCount = count($parameters);

        if (! ($paramCount >= 1 && $paramCount <= 2)) {
            throw new BadMethodCallException(sprintf('Method %s::%s expects 1 or 2 parameters, %d parameters given.', static::class, $name, $paramCount));
        }

        [$value] = $parameters;

        if ($paramCount === 2) {
            return (bool) $parameters[1] ? $this->addHeaderClaim($name, $value) : $this->addPayloadClaim($name, $value);
        } else {
            return $this->addClaim($name, $value);
        }
    }

    /**
     * Checks if claim belongs in header.
     *
     * @param string $key
     * @return bool
     */
    protected function isHeaderClaim($key)
    {
        return in_array($key, $this->headerClaims);
    }

    /**
     * Checks if claim belongs in payload.
     *
     * @param string $key
     * @return bool
     */
    protected function isPayloadClaim($key)
    {
        return in_array($key, $this->payloadClaims);
    }
}
