<?php

namespace LittleApps\LittleJWT\Guards\Adapters\Concerns;

use LittleApps\LittleJWT\Build\Builders\GuardBuilder;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Response as ResponseFactory;

trait BuildsJwt {
    /**
     * Builds a JWT for a user.
     *
     * @param Authenticatable $user
     * @param array $payloadClaims Any extra claims to add to JWT.
     * @param array $headerClaims Any extra claims to add to JWT.
     * @return JWT
     */
    public function buildJwtForUser(Authenticatable $user, array $payloadClaims = [], array $headerClaims = []) {
        $builder = new GuardBuilder($this->container, $user, $payloadClaims, $headerClaims);

        return $this->jwt->createJWT([$builder, 'build']);
    }

    /**
     * Creates a JWT response for an Authenticatable instance.
     *
     * @param Authenticatable|null $user The user to generate the JWT for.
     * @return \Illuminate\Http\JsonResponse Returns response with JWT
     */
    public function createJwtResponse(Authenticatable $user) {
        $jwt = $this->buildJwtForUser($user);

        return ResponseFactory::withJwt($jwt);
    }
}
