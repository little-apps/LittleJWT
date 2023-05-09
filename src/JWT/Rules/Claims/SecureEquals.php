<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JsonWebToken;

/**
 * This rule uses hash_equals to perform a comparison on claim value.
 */
class SecureEquals extends Rule
{
    /**
     * Expected claim value.
     *
     * @var mixed
     */
    protected $expected;

    /**
     * Initializes secure equals rule.
     *
     * @param string $key Claim key
     * @param mixed $expected Expected claim value
     * @param bool $inHeader If true, pulls claim value from header.
     */
    public function __construct($key, $expected, $inHeader = false)
    {
        parent::__construct($key, $inHeader);

        $this->expected = $expected;
    }

    /**
     * @inheritDoc
     */
    protected function checkClaim(JsonWebToken $jwt, $value)
    {
        return hash_equals($this->expected, $value);
    }

    /**
     * @inheritDoc
     */
    protected function formatMessage()
    {
        return sprintf("The ':key' claim does not match expected value '%s'.", $this->expected);
    }
}
