<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Support\Arr;

use LittleApps\LittleJWT\JWT\ClaimManager;

class ClaimManagerBuilder
{
    const PART_HEADER = 'header';
    const PART_PAYLOAD = 'payload';

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
     * @return ClaimManager
     */
    public function buildClaimManagerForHeader(array $claims) {
        return $this->buildClaimManagerFor(static::PART_HEADER, $claims);
    }

    /**
     * Builds a ClaimManager for the payload claims.
     *
     * @param array $claims
     * @return ClaimManager
     */
    public function buildClaimManagerForPayload(array $claims) {
        return $this->buildClaimManagerFor(static::PART_PAYLOAD, $claims);
    }

    /**
     * Builds a ClaimManager for a part.
     *
     * @param string $part One of PART_* constants. The part name is used to lookup available mutators.
     * @param array $claims Associative array of claims
     * @return ClaimManager
     */
    public function buildClaimManagerFor(string $part, array $claims) {
        $sorted = Arr::sortRecursive($claims);
        $mutators = $this->getMutatorsFor($part);

        return new ClaimManager($sorted, $mutators);
    }

    /**
     * Gets the mutator options for a part.
     *
     * @param string $part One of PART_* constants.
     * @return array
     */
    public function getMutatorsFor(string $part) {
        return Arr::get($this->mutators, $part, []);
    }
}
