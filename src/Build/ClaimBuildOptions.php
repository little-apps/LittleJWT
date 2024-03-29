<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Support\Traits\Macroable;

class ClaimBuildOptions
{
    use Macroable {
        __call as macroCall;
    }

    public const PART_HEADERS = 'headers';
    public const PART_PAYLOAD = 'payload';

    /**
     * Builder used for this claim.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Part this claim belongs to
     *
     * @var string One of PART_* constants
     */
    protected $part;

    /**
     * Claim key
     *
     * @var string
     */
    protected $key;

    /**
     * Unserialized value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Initializes ClaimBuildOptions
     *
     * @param Builder $builder
     * @param string $part
     * @param string $key
     * @param mixed $value
     */
    public function __construct(Builder $builder, string $part, string $key, $value)
    {
        $this->builder = $builder;
        $this->part = $part;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Gets the Builder used for this claim.
     *
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Gets the part this claim belongs to (headers or payload)
     *
     * @return string
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Gets the claim key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets the unserialized claim value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Passes method calls to macro or builder.
     *
     * @param string $name Method name
     * @param array $arguments Method arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (static::hasMacro($name)) {
            return $this->macroCall($name, $arguments);
        }

        return $this->getBuilder()->$name(...$arguments);
    }
}
