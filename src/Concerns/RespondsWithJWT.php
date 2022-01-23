<?php

namespace LittleApps\LittleJWT\Concerns;

use DateTimeInterface;

use LittleApps\LittleJWT\JWT\JWT;

use LittleApps\LittleJWT\Utils\ResponseBuilder;

use Illuminate\Support\Carbon;
use Illuminate\Http\Response;

trait RespondsWithJWT {
    /**
     * Builds the JWT array using a JWT instance.
     *
     * @param JWT $jwt
     * @return array
     */
    protected function buildJsonResponseWithJwt(JWT $jwt) {
        return ResponseBuilder::buildFromJwt($jwt);
    }

    /**
     * Builds the JWT array structure for JSON.
     *
     * @param string $token
     * @param DateTimeInterface $expires
     * @return array
     */
    protected function buildJsonResponseWithToken(string $token, DateTimeInterface $expires) {
        return Response::buildFromToken($token, $expires);
    }

    /**
     * Attaches JWT to Authorization response header.
     *
     * @param Response $response
     * @param JWT|string $token
     * @return Response
     */
    protected function attachJwtToResponseHeader(Response $response, $token) {
        return $response->header('Authorization', sprintf('Bearer %s', (string) $token));
    }
}
