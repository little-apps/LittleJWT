<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JsonWebToken;

class ArrayEquals extends Rule
{
    /**
     * The array of expected claim value.
     *
     * @var array
     */
    protected $expected;

    /**
     * If true, the claim value array must be the exact same as the expected array.
     *
     * @var bool
     */
    protected $strict;

    /**
     * Initializes Equals rule
     *
     * @param string $key Claim key.
     * @param array $expected Expected claim value.
     * @param bool $strict If true, strict comparsion is used.
     * @param bool $inHeader If true, checks header instead of payload.
     */
    public function __construct(string $key, array $expected, $strict = false, $inHeader = false)
    {
        parent::__construct($key, $inHeader);

        $this->expected = $expected;
        $this->strict = $strict;
    }

    /**
     * @inheritDoc
     */
    protected function checkClaim(JsonWebToken $jwt, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        if ($this->strict) {
            if (count($value) !== count($this->expected)) {
                return false;
            }

            array_multisort($value);
            array_multisort($this->expected);

            return $value === $this->expected;
        } else {
            return count(array_diff($value, $this->expected)) < count($value);
        }
    }

    /**
     * @inheritDoc
     */
    protected function formatMessage()
    {
        return sprintf("The ':key' claim is not an array or doesn't %smatch the expected array: %s", $this->strict ? ' exactly' : '', json_encode($this->expected));
    }
}
