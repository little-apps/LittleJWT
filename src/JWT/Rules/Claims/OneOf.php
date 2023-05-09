<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\JWT\JsonWebToken;

class OneOf extends Rule
{
    /**
     * Possible claim values.
     *
     * @var list<mixed>
     */
    protected $haystack;

    /**
     * If true, uses strict comparison when comparing claim value with possible claim values.
     *
     * @var bool
     */
    protected $strict;

    public function __construct($key, array $haystack, $strict = true, $inHeader = false)
    {
        parent::__construct($key, $inHeader);

        $this->haystack = $haystack;
        $this->strict = $strict;
    }

    /**
     * @inheritDoc
     */
    protected function checkClaim(JsonWebToken $jwt, $value)
    {
        return in_array($value, $this->haystack, $this->strict);
    }

    /**
     * @inheritDoc
     */
    protected function formatMessage()
    {
        return "The ':key' claim is not one of expected values.";
    }
}
