<?php

namespace LittleApps\LittleJWT\JWT\Rules;

use LittleApps\LittleJWT\JWT\JsonWebToken;

class ContainsClaims extends Rule
{
    /**
     * Expected claim keys.
     *
     * @var array<string>
     */
    protected $expected;

    /**
     * Whether claims should be in header.
     *
     * @var bool
     */
    protected $inHeader;

    /**
     * If true, every claim key must exist only once.
     *
     * @var bool
     */
    protected $strict;

    public function __construct(iterable $expected, $inHeader = false, $strict = false)
    {
        $this->expected = $expected;
        $this->inHeader = (bool) $inHeader;
        $this->strict = (bool) $strict;
    }

    /**
     * @inheritDoc
     */
    public function passes(JsonWebToken $jwt)
    {
        $claims = $this->inHeader ? $jwt->getHeaders() : $jwt->getPayload();

        $found = 0;

        foreach ($this->expected as $key) {
            if ($claims->has($key)) {
                $found++;
            }
        }

        if ($this->strict) {
            // If in strict mode, only return true if # found is the exact same as the number expected and as how many claims there are.
            return $found === count($this->expected) && $found === count($claims);
        } else {
            return $found >= count($this->expected);
        }
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'The JWT did not have the expected claim keys.';
    }
}
