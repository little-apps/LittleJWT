<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JsonWebToken;

class Equals extends Rule
{
    /**
     * The expected claim value.
     *
     * @var mixed
     */
    protected $expected;

    /**
     * If true, strict comparison is used.
     *
     * @var bool
     */
    protected $strict;

    /**
     * Initializes Equals rule
     *
     * @param string $key Claim key.
     * @param mixed $expected Expected claim value.
     * @param bool $strict If true, strict comparsion is used.
     * @param bool $inHeader If true, checks header instead of payload.
     */
    public function __construct($key, $expected, $strict = true, $inHeader = false)
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
        return $this->strict ? $value === $this->expected : $value == $this->expected;
    }

    /**
     * @inheritDoc
     */
    protected function formatMessage()
    {
        return sprintf("The ':key' claim does not match expected value '%s'.", $this->expected);
    }
}
