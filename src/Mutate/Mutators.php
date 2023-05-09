<?php

namespace LittleApps\LittleJWT\Mutate;

use BadMethodCallException;

use Illuminate\Support\Traits\Macroable;

class Mutators
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Mutators that will be applied to either header or payload claims.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $global;

    /**
     * Mutators that will be applied to header claims.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $headers;

    /**
     * Mutators that will be applied to payload claims.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $payload;

    public function __construct(array $global = [], array $headers = [], array $payload = [])
    {
        $this->global = collect($global);
        $this->headers = collect($headers);
        $this->payload = collect($payload);
    }

    /**
     * Adds a claim mutator for either the header or payload.
     *
     * @param string $key Claim key
     * @param mixed $value Mutator
     * @return $this
     */
    public function add($key, $value)
    {
        $this->global[$key] = $value;

        return $this;
    }

    /**
     * Adds a mutator for header claim.
     *
     * @param string $key Claim key
     * @param mixed $value Mutator
     * @return $this
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Adds a mutator for payload claim.
     *
     * @param string $key Claim key
     * @param mixed $value Mutator
     * @return $this
     */
    public function addPayload($key, $value)
    {
        $this->payload[$key] = $value;

        return $this;
    }

    /**
     * Checks if mutator for claim key exists.
     *
     * @param string $key Claim key
     * @return bool True if mutator exists.
     */
    public function has($key)
    {
        return $this->hasGlobal($key) || $this->hasHeader($key) || $this->hasPayload($key);
    }

    /**
     * Checks if global mutator for claim key exists.
     *
     * @param string $key Claim key
     * @return bool True if mutator exists.
     */
    public function hasGlobal($key)
    {
        return isset($this->global[$key]);
    }

    /**
     * Checks if mutator for header claim key exists.
     *
     * @param string $key Claim key
     * @return bool True if mutator exists.
     */
    public function hasHeader($key)
    {
        return isset($this->headers[$key]);
    }

    /**
     * Checks if mutator for payload claim key exists.
     *
     * @param string $key Claim key
     * @return bool True if mutator exists.
     */
    public function hasPayload($key)
    {
        return isset($this->payload[$key]);
    }

    /**
     * Removes mutator for claim key from either globals, header, or payload list.
     *
     * @param string $key
     * @return $this
     */
    public function remove($key)
    {
        if ($this->hasGlobal($key)) {
            $this->removeGlobal($key);
        }

        if ($this->hasHeader($key)) {
            $this->removeHeader($key);
        }

        if ($this->hasPayload($key)) {
            $this->removePayload($key);
        }

        return $this;
    }

    /**
     * Removes a mutator for claim key from globals.
     *
     * @param string $key Claim key
     * @return $this
     */
    public function removeGlobal($key)
    {
        unset($this->global[$key]);

        return $this;
    }

    /**
     * Removes a mutator for header claim key.
     *
     * @param string $key Claim key
     * @return $this
     */
    public function removeHeader($key)
    {
        unset($this->headers[$key]);

        return $this;
    }

    /**
     * Removes a mutator for payload claim key.
     *
     * @param string $key Claim key
     * @return $this
     */
    public function removePayload($key)
    {
        unset($this->payload[$key]);

        return $this;
    }

    /**
     * Gets the global mutators.
     *
     * @param string|null $key Claim key or null to get all definitions. (default: null)
     * @return mixed|array
     */
    public function getGlobal(?string $key = null)
    {
        return ! is_null($key) ? $this->global[$key] : $this->global->all();
    }

    /**
     * Gets the header mutators.
     *
     * @param string|null $key Claim key or null to get all definitions. (default: null)
     * @return mixed|array
     */
    public function getHeaders(?string $key = null)
    {
        return ! is_null($key) ? $this->headers[$key] : $this->headers->all();
    }

    /**
     * Gets the payload mutators.
     *
     * @param string|null $key Claim key or null to get all definitions. (default: null)
     * @return mixed|array
     */
    public function getPayload(?string $key = null)
    {
        return ! is_null($key) ? $this->payload[$key] : $this->payload->all();
    }

    /**
     * Gets the global, header and payload mutators as one array.
     * This is not recommended as a claim key can exists in either list.
     *
     * @return array
     */
    public function all()
    {
        return $this->global->merge($this->headers->merge($this->payload))->all();
    }

    /**
     * Merges other Mutators instance with this one.
     * The mutators in Mutators argument take precendence over mutators in this instance.
     *
     * @param self $mutators
     * @return $this
     */
    public function merge(self $mutators)
    {
        $this->global = $this->global->merge($mutators->getGlobal());
        $this->headers = $this->headers->merge($mutators->getHeaders());
        $this->payload = $this->payload->merge($mutators->getPayload());

        return $this;
    }

    /**
     * Allows for checking if a mutator exists in either list using isset() on instance property.
     * Example:
     *   $exists = isset($mutators->iat); // Checks if 'iat' mutator exists.
     *
     * @param string $key Claim key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Allows for existing mutator to be removed from either list using unset() on instance property.
     * Example:
     *   unset($mutators->iat); // Removes 'iat' mutator.
     *
     * @param string $key Claim key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * Allows for mutator to be added by setting it as a property.
     * All mutators set as a property are automatically added to global mutators list.
     * Example:
     *   $mutators->iat = 'timestamp'; // Adds 'iat' timestamp mutator to global mutators.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->add($key, $value);
    }

    /**
     * Allows for claims to be retrieved as properties.
     * Checks header, payload, and global mutators in order.
     *
     * @param string $key Claim key
     * @return mixed|null Mutator or null if not found.
     */
    public function __get($key)
    {
        if ($this->hasHeader($key)) {
            return $this->headers[$key];
        }

        if ($this->hasPayload($key)) {
            return $this->payload[$key];
        }

        if ($this->hasGlobal($key)) {
            return $this->global[$key];
        }

        return null;
    }

    /**
     * Adds mutator for claim key (the method name) to either global, headers, or payload list.
     * Example:
     *   $mutators->iat('timestamp'); // Adds 'iat' timestamp mutator to global mutators.
     *   $mutators->iat('timestamp', 'header'); // Adds 'iat' timestamp mutator to header mutators.
     *   $mutators->iat('timestamp', 'payload'); // Adds 'iat' timestamp mutator to payload mutators.
     *
     * @param string $name Claim key
     * @param array $arguments Arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (static::hasMacro($name)) {
            return $this->macroCall($name, $arguments);
        }

        $argCount = count($arguments);

        if (! ($argCount >= 1 && $argCount <= 2)) {
            throw new BadMethodCallException(sprintf('Method %s::%s expects 1 or 2 parameters, %d parameters given.', static::class, $name, $argCount));
        }

        [$mutator] = $arguments;

        if ($argCount === 2) {
            if ($arguments[1] === 'header') {
                return $this->addHeader($name, $mutator);
            } elseif ($arguments[1] === 'payload') {
                return $this->addPayload($name, $mutator);
            }
        }

        return $this->add($name, $mutator);
    }
}
