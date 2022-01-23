<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\Contracts\Rule as RuleContract;
use LittleApps\LittleJWT\JWT\JWT;

use Illuminate\Support\Str;

abstract class Rule implements RuleContract {
    protected $key;

    protected $inHeader;

    /**
     * Constructor for ClaimRule.
     *
     * @param string $key Claim key
     * @param bool $inHeader If true, gets claim from header.
     */
    protected function __construct($key, $inHeader)
    {
        $this->key = $key;
        $this->inHeader = $inHeader;
    }

    public function passes(JWT $jwt) {
        // Checks that claim exists before continuing.
        // Returns true if claim doesn't exist so verification continues.
        // If claim key is required, use the ContainsClaims rule.
        if (!$this->hasClaim($jwt))
            return true;

        return $this->checkClaim($jwt, $this->getValue($jwt));
    }

    public function message() {
        $replace = [
            ':key' => $this->getKey()
        ];

        return Str::replace(array_keys($replace), array_values($replace), $this->formatMessage());
    }

    /**
     * Gets the claim key
     *
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Gets if claim is in header.
     *
     * @return bool
     */
    public function getInHeader() {
        return $this->inHeader;
    }

    /**
     * Checks that a claim is valid, if it exists.
     *
     * @param JWT $jwt
     * @param mixed $value
     * @return boolean
     */
    abstract protected function checkClaim(JWT $jwt, $value);

    /**
     * Formats a message for a claim check.
     *
     * @return string
     */
    protected function formatMessage() {
        return "Claim with key ':key' is invalid.";
    }

    /**
     * Gets the claims from either headers or payload.
     *
     * @param JWT $jwt
     * @return \LittleApps\LittleJWT\JWT\ClaimManager
     */
    protected function getClaims(JWT $jwt) {
        return $this->inHeader ? $jwt->getHeaders() : $jwt->getPayload();
    }

    /**
     * Checks if JWT has claim.
     *
     * @param JWT $jwt
     * @return boolean
     */
    protected function hasClaim(JWT $jwt) {
        return $this->getClaims($jwt)->has($this->getKey());
    }

    /**
     * Gets the claim value from JWT
     *
     * @param JWT $jwt
     * @return mixed
     */
    protected function getValue(JWT $jwt) {
        return $this->getClaims($jwt)->get($this->getKey());
    }
}
