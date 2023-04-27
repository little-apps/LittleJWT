<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Support\Arr;

use LittleApps\LittleJWT\JWT\ClaimManager;

class ClaimManagerBuilder
{
    public const PART_HEADER = 'header';
    public const PART_PAYLOAD = 'payload';

    protected $mutators;

    /**
     * Initializes a ClaimManagerBuilder
     *
     * @param array $mutators Configuration options for mutators
     */
    public function __construct(array $mutators)
    {
        $this->mutators = $mutators;
    }

    /**
     * Builds a ClaimManager for the header claims.
     *
     * @param array $claims Header claims
     * @param array $mutators Additional header mutators to use
     * @return ClaimManager
     */
    public function buildClaimManagerForHeader(array $claims, array $mutators)
    {
        return $this->buildClaimManagerFor(static::PART_HEADER, $claims, $mutators);
    }

    /**
     * Builds a ClaimManager for the payload claims.
     *
     * @param array $claims
     * @param array $mutators Additional payload mutators to use
     * @return ClaimManager
     */
    public function buildClaimManagerForPayload(array $claims, array $mutators)
    {
        return $this->buildClaimManagerFor(static::PART_PAYLOAD, $claims, $mutators);
    }

    /**
     * Builds a ClaimManager for a part.
     *
     * @param string $part One of PART_* constants. The part name is used to lookup available mutators.
     * @param array $claims Associative array of claims
     * @param array $mutators Additional mutators to use.
     * @return ClaimManager
     */
    public function buildClaimManagerFor(string $part, array $claims, array $mutators)
    {
        $sorted = Arr::sortRecursive($claims);
        $mutators = array_merge($this->getMutatorsFor($part), $mutators);

        return new ClaimManager($sorted, $mutators);
    }

    /**
     * Gets the mutator options for a part.
     *
     * @param string $part One of PART_* constants.
     * @return array
     */
    public function getMutatorsFor(string $part)
    {
        return Arr::get($this->mutators, $part, []);
    }
}
