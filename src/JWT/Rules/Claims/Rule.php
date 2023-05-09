<?php

namespace LittleApps\LittleJWT\JWT\Rules\Claims;

use LittleApps\LittleJWT\Contracts\Rule as RuleContract;

use LittleApps\LittleJWT\JWT\JsonWebToken;

abstract class Rule implements RuleContract
{
    /**
     * Claim key to check
     *
     * @var string
     */
    protected $key;

    /**
     * If true, gets claim value from header.
     *
     * @var bool
     */
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

    /**
     * @inheritDoc
     */
    public function passes(JsonWebToken $jwt)
    {
        // Checks that claim exists before continuing.
        // Returns true if claim doesn't exist so verification continues.
        // If claim key is required, use the ContainsClaims rule.
        if (! $this->hasClaim($jwt)) {
            return true;
        }

        return $this->checkClaim($jwt, $this->getValue($jwt));
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        $replace = [
            ':key' => $this->getKey(),
        ];

        // Doesn't use Str::replace because Laravel 7.x doesn't support it.
        return str_replace(array_keys($replace), array_values($replace), $this->formatMessage());
    }

    /**
     * Gets the claim key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets if claim is in header.
     *
     * @return bool
     */
    public function getInHeader()
    {
        return $this->inHeader;
    }

    /**
     * Checks that a claim is valid, if it exists.
     *
     * @param JsonWebToken $jwt
     * @param mixed $value
     * @return bool
     */
    abstract protected function checkClaim(JsonWebToken $jwt, $value);

    /**
     * Formats a message for a claim check.
     *
     * @return string
     */
    protected function formatMessage()
    {
        return "Claim with key ':key' is invalid.";
    }

    /**
     * Gets the claims from either headers or payload.
     *
     * @param JsonWebToken $jwt
     * @return \LittleApps\LittleJWT\JWT\ClaimManager
     */
    protected function getClaims(JsonWebToken $jwt)
    {
        return $this->inHeader ? $jwt->getHeaders() : $jwt->getPayload();
    }

    /**
     * Checks if JWT has claim.
     *
     * @param JsonWebToken $jwt
     * @return bool
     */
    protected function hasClaim(JsonWebToken $jwt)
    {
        return $this->getClaims($jwt)->has($this->getKey());
    }

    /**
     * Gets the claim value from JWT
     *
     * @param JsonWebToken $jwt
     * @return mixed
     */
    protected function getValue(JsonWebToken $jwt)
    {
        return $this->getClaims($jwt)->get($this->getKey());
    }
}
