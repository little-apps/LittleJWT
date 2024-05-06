<?php

namespace LittleApps\LittleJWT\Build;

use BadMethodCallException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use LittleApps\LittleJWT\Contracts\BuildsJWTClaims;
use Illuminate\Support\Traits\Macroable;
use LittleApps\LittleJWT\Core\Concerns\CreatesCallbackBuilder;
use LittleApps\LittleJWT\Factories\ClaimManagerBuilder;
use LittleApps\LittleJWT\JWT\ClaimManager;
use LittleApps\LittleJWT\JWT\ClaimManagers;
use LittleApps\LittleJWT\JWT\ImmutableClaimManager;

class Options implements BuildsJWTClaims
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Header claims
     *
     * @var ClaimManager
     */
    protected $headers;

    /**
     * Payload claims.
     *
     * @var ClaimManager
     */
    protected $payload;

    /**
     * Claim keys that belong in header.
     *
     * @var array
     */
    protected $reservedHeaderKeys;

    /**
     * Claim keys that belong in payload.
     *
     * @var array
     */
    protected $reservedPayloadKeys;

    /**
     * Additional buildables
     *
     * @var list<callable>
     */
    protected $buildables;

    /**
     * Initializes Builder instance.
     */
    public function __construct()
    {
        $this->headers = new ClaimManager(ClaimManager::PART_HEADER, []);
        $this->payload = new ClaimManager(ClaimManager::PART_PAYLOAD, []);
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
     * @return $this
     */
    public function addHeaderClaim($key, $value)
    {
        $this->headers->set($key, $value);

        return $this;
    }

    /**
     * Adds a claim to the payload.
     *
     * @param string $key Claim key
     * @param mixed $value Claim value. Will be sent through ClaimsSerializer for serialization.
     * @return $this
     */
    public function addPayloadClaim($key, $value)
    {
        $this->payload->set($key, $value);

        return $this;
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
    public function getHeaders(): array
    {
        return $this->headers->mapToValues()->all();
    }

    /**
     * Gets the headers claim options.
     *
     * @return list<ClaimBuildOptions>
     */
    public function getHeadersOptions()
    {
        return $this->headers->toArray();
    }

    /**
     * Gets the payload claims.
     *
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload->mapToValues()->all();
    }

    /**
     * Gets the payload claim options.
     *
     * @return list<ClaimBuildOptions>
     */
    public function getPayloadOptions()
    {
        return $this->payload->toArray();
    }

    /**
     * Gets the JWT claims.
     *
     * @return ClaimManagers
     */
    public function getClaimManagers(): ClaimManagers {
        return new ClaimManagers(
            new ImmutableClaimManager(ClaimManager::PART_HEADER, $this->getHeaders()),
            new ImmutableClaimManager(ClaimManager::PART_PAYLOAD, $this->getPayload())
        );
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
        return $this->hasHeaderClaim($key) || $this->hasPayloadClaim($key);
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
     * @return $this|mixed
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
     * Gets the reserved header keys
     *
     * @return list<string>
     */
    protected function getReservedHeaderKeys(): array {
        return config('littlejwt.builder.claims.header', []);
    }

    /**
     * Gets the reserved payload keys
     *
     * @return list<string>
     */
    protected function getReservedPayloadKeys(): array {
        return config('littlejwt.builder.claims.payload', []);
    }

    /**
     * Checks if claim belongs in header.
     *
     * @param string $key
     * @return bool
     */
    protected function isHeaderClaim($key)
    {
        return in_array($key, $this->getReservedHeaderKeys());
    }

    /**
     * Checks if claim belongs in payload.
     *
     * @param string $key
     * @return bool
     */
    protected function isPayloadClaim($key)
    {
        return in_array($key, $this->getReservedPayloadKeys());
    }
}
