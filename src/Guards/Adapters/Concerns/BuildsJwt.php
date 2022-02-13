<?php

namespace LittleApps\LittleJWT\Guards\Adapters\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;

use Illuminate\Support\Facades\Response as ResponseFactory;
use LittleApps\LittleJWT\Build\Buildables\GuardBuildable;

trait BuildsJwt
{
    /**
     * Builds a JWT for a user.
     *
     * @param Authenticatable $user
     * @param array $payloadClaims Any extra claims to add to JWT.
     * @param array $headerClaims Any extra claims to add to JWT.
     * @return JWT
     */
    public function buildJwtForUser(Authenticatable $user, array $payloadClaims = [], array $headerClaims = [])
    {
        $buildable = new GuardBuildable($user, $payloadClaims, $headerClaims);

        return $this->jwt->createJWT([$buildable, 'build']);
    }

    /**
     * Creates a JWT response for an Authenticatable instance.
     *
     * @param Authenticatable|null $user The user to generate the JWT for.
     * @return \Illuminate\Http\JsonResponse Returns response with JWT
     */
    public function createJwtResponse(Authenticatable $user)
    {
        $jwt = $this->buildJwtForUser($user);

        return ResponseFactory::withJwt($jwt);
    }
}
