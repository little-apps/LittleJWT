<?php

namespace LittleApps\LittleJWT\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

use LittleApps\LittleJWT\JWT\JWT;
use LittleApps\LittleJWT\Contracts\Rule;

class TestRule implements Rule {
    protected $baseRule;

    protected $message;

    protected $assertPasses;

    /**
     * Constructor for TestRule
     *
     * @param Rule $baseRule Rule to test
     * @param bool $assertPasses If true, asserts test passes. If false, asserts test fails.
     * @param string $message Message to use for PHPUnit assert.
     */
    public function __construct(Rule $baseRule, $assertPasses, $message = '')
    {
        $this->baseRule = $baseRule;
        $this->message = $message;
        $this->assertPasses = (bool) $assertPasses;
    }

    /**
     * Checks if JWT passes rule.
     *
     * @param \LittleApps\LittleJWT\JWT\JWT $jwt
     * @return bool True if JWT passes rule check.
     */
    public function passes(JWT $jwt) {
        $passes = $this->baseRule->passes($jwt);

        if ($this->assertPasses)
            PHPUnit::assertTrue($passes, $this->message);
        else
            PHPUnit::assertFalse($passes, $this->message);

        return $passes;
    }

    /**
     * Gets the error message for when the rule fails.
     *
     * @return string
     */
    public function message() {
        return $this->baseRule->message();
    }

    /**
     * Gets the key to be used for the error messages.
     *
     * @return string
     */
    public function getKey() {
        return $this->baseRule->getKey();
    }
}
