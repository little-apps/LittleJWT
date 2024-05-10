<?php

namespace LittleApps\LittleJWT\Testing;

use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class TestMutator implements Mutator
{
    /**
     * Serialize callback to use.
     *
     * @var callable(mixed, string, array, JsonWebToken): mixed
     */
    protected $serializeCallback;

    /**
     * Unserialize callback to use.
     *
     * @var callable(mixed, string, array, JsonWebToken): mixed
     */
    protected $unserializeCallback;

    /**
     * Initalizes test mutator.
     *
     * @param  callable(mixed $value, string $key, array $args, JsonWebToken $jwt): mixed  $serializeCallback  Serialize callback
     * @param  callable(mixed $value, string $key, array $args, JsonWebToken $jwt): mixed  $unserializeCallback  Unserialize callback
     */
    public function __construct(callable $serializeCallback, callable $unserializeCallback)
    {
        $this->serializeCallback = $serializeCallback;
        $this->unserializeCallback = $unserializeCallback;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return call_user_func($this->serializeCallback, $value, $key, $args, $jwt);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        return call_user_func($this->unserializeCallback, $value, $key, $args, $jwt);
    }
}
