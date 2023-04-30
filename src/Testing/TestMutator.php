<?php

namespace LittleApps\LittleJWT\Testing;

use LittleApps\LittleJWT\Contracts\Mutator;

class TestMutator implements Mutator
{
    /**
     * Serialize callback to use.
     *
     * @var callable(mixed, string $key, array $args, array $claims): mixed
     */
    protected $serializeCallback;

    /**
     * Unserialize callback to use.
     *
     * @var callable(mixed, string $key, array $args, array $claims): mixed
     */
    protected $unserializeCallback;

    /**
     * Initalizes test mutator.
     *
     * @param callable(mixed $value, string $key, array $args, array $claims): mixed $serializeCallback Serialize callback
     * @param callable(mixed $value, string $key, array $args, array $claims): mixed $unserializeCallback Unserialize callback
     */
    public function __construct(callable $serializeCallback, callable $unserializeCallback)
    {
        $this->serializeCallback = $serializeCallback;
        $this->unserializeCallback = $unserializeCallback;
    }

    /**
     * Serializes claim value
     *
     * @param mixed $value Unserialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return string|array|int
     */
    public function serialize($value, string $key, array $args, array $claims)
    {
        return call_user_func($this->serializeCallback, $value, $key, $args, $claims);
    }

    /**
     * Unserializes claim value
     *
     * @param string|array|int $value Serialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return mixed
     */
    public function unserialize($value, string $key, array $args, array $claims)
    {
        return call_user_func($this->unserializeCallback, $value, $key, $args, $claims);
    }
}
