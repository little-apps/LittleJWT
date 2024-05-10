<?php

namespace LittleApps\LittleJWT\Guards\Adapters\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Response as ResponseFactory;
use LittleApps\LittleJWT\Build\Buildables\GuardBuildable;
use LittleApps\LittleJWT\JWT\SignedJsonWebToken;

trait BuildsJwt
{
    /**
     * Builds a JWT for a user.
     *
     * @param Authenticatable $user
     * @param array $payloadClaims Any extra claims to add to JWT.
     * @param array $headerClaims Any extra claims to add to JWT.
     * @return SignedJsonWebToken
     */
    public function buildJwtForUser(Authenticatable $user, array $payloadClaims = [], array $headerClaims = [])
    {
        $buildable = new GuardBuildable($user, $payloadClaims, $headerClaims);

        return $this->getHandler()->createSigned($buildable);
    }

    /**
     * Creates a JWT response for an Authenticatable instance.
     *
     * @param Authenticatable $user The user to generate the JWT for.
     * @return \Illuminate\Http\JsonResponse Returns response with JWT
     */
    public function createJwtResponse(Authenticatable $user)
    {
        $jwt = $this->buildJwtForUser($user);

        return ResponseFactory::withJwt($jwt);
    }
}
