<?php

namespace LittleApps\LittleJWT\Testing;

use LittleApps\LittleJWT\Contracts\Mutator;

class TestMutator implements Mutator
{
    protected $serializeCallback;
    protected $unserializeCallback;

    public function __construct(callable $serializeCallback, callable $unserializeCallback)
    {
        $this->serializeCallback = $serializeCallback;
        $this->unserializeCallback = $unserializeCallback;
    }

    /**
     * Serializes claim value
     *
     * @param mixed $value Claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @return mixed
     */
    public function serialize($value, string $key, array $args)
    {
        return call_user_func($this->serializeCallback, $value, $key, $args);
    }

    /**
     * Unserializes claim value
     *
     * @param mixed $value Claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @return mixed
     */
    public function unserialize($value, string $key, array $args)
    {
        return call_user_func($this->unserializeCallback, $value, $key, $args);
    }
}
