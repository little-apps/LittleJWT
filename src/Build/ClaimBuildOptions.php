<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Support\Traits\Macroable;

final class ClaimBuildOptions
{
    use Macroable;

    public const PART_HEADERS = 'headers';

    public const PART_PAYLOAD = 'payload';

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
     * @param  mixed  $value
     */
    public function __construct(string $part, string $key, $value)
    {
        $this->part = $part;
        $this->key = $key;
        $this->value = $value;
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
        return $this->value instanceof self ? $this->value->getValue() : $this->value;
    }
}
