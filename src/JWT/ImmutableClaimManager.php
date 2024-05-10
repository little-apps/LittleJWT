<?php

namespace LittleApps\LittleJWT\JWT;

use RuntimeException;

final class ImmutableClaimManager extends ClaimManager
{
    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException Thrown when called.
     */
    public function set(string $key, $value)
    {
        throw new RuntimeException('Attempt to mutate immutable '.self::class.' object.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException Thrown when called.
     */
    public function unset(string $key): static
    {
        throw new RuntimeException('Attempt to mutate immutable '.self::class.' object.');
    }
}
