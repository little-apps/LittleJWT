<?php

namespace LittleApps\LittleJWT\JWT;

final class ClaimManagers
{
    /**
     * Initializes ClaimManagers instance
     */
    public function __construct(
        public readonly ClaimManager $header,
        public readonly ClaimManager $payload,
    ) {}

    /**
     * Merges multiple ClaimManagers together
     * Note: If multiple claim managers have the same key, the latter value is used.
     *
     * @param  ClaimManagers  ...$claimManagers  ClaimManagers to merge.
     * @return static
     */
    public static function merge(self ...$claimManagers)
    {
        $headers = [];
        $payload = [];

        foreach ($claimManagers as $claimManager) {
            array_push($headers, $claimManager->header);
            array_push($payload, $claimManager->payload);
        }

        return new self(
            ClaimManager::merge(ClaimManager::PART_HEADER, ...$headers),
            ClaimManager::merge(ClaimManager::PART_PAYLOAD, ...$payload)
        );
    }
}
