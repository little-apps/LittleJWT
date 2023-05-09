<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Support\Arr;

use LittleApps\LittleJWT\JWT\ClaimManager;

class ClaimManagerBuilder
{
    public const PART_HEADER = 'header';
    public const PART_PAYLOAD = 'payload';

    /**
     * Initializes a ClaimManagerBuilder
     *
     */
    public function __construct()
    {
    }

    /**
     * Builds a ClaimManager for the header claims.
     *
     * @param array $claims Header claims
     * @return ClaimManager
     */
    public function buildClaimManagerForHeader(array $claims)
    {
        return $this->buildClaimManagerFor(static::PART_HEADER, $claims);
    }

    /**
     * Builds a ClaimManager for the payload claims.
     *
     * @param array $claims
     * @return ClaimManager
     */
    public function buildClaimManagerForPayload(array $claims)
    {
        return $this->buildClaimManagerFor(static::PART_PAYLOAD, $claims);
    }

    /**
     * Builds a ClaimManager for a part.
     *
     * @param array $claims Associative array of claims
     * @return ClaimManager
     */
    public function buildClaimManagerFor(string $part, array $claims)
    {
        $sorted = Arr::sortRecursive($claims);

        return new ClaimManager($part, $sorted);
    }
}
