<?php

namespace LittleApps\LittleJWT\Concerns;

use DateTimeInterface;
use Illuminate\Http\Response;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\Utils\ResponseBuilder;

trait RespondsWithJWT
{
    /**
     * Builds the JWT array using a JWT instance.
     *
     * @return array
     */
    protected function buildJsonResponseWithJwt(JsonWebToken $jwt)
    {
        return ResponseBuilder::buildFromJwt($jwt);
    }

    /**
     * Builds the JWT array structure for JSON.
     *
     * @return array
     */
    protected function buildJsonResponseWithToken(string $token, DateTimeInterface $expires)
    {
        return ResponseBuilder::buildFromToken($token, $expires);
    }

    /**
     * Attaches JWT to Authorization response header.
     *
     * @param  JsonWebToken|string  $token
     * @return Response
     */
    protected function attachJwtToResponseHeader(Response $response, $token)
    {
        return $response->header('Authorization', sprintf('Bearer %s', (string) $token));
    }
}
