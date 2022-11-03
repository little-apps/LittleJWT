<?php

namespace LittleApps\LittleJWT\Concerns;

use LittleApps\LittleJWT\JWT\JWT;

trait JWTHelpers
{
    /**
     * Generates a hash for class.
     *
     * @param object|string $model If an object, resolves the class of object.
     * @return string
     */
    protected function hashSubjectModel($model)
    {
        $class = is_object($model) ? get_class($model) : $model;

        $hash = hash('sha256', $class);

        return $hash;
    }

    /**
     * Gets a unique identifier for the JWT
     *
     * @param JWT $jwt
     * @return string
     */
    protected function getUniqueId(JWT $jwt)
    {
        // Use jti claim (if it exists)
        if ($jwt->getPayload()->has('jti')) {
            return (string) $jwt->getPayload()->get('jti');
        }

        // Otherwise, use sha1 of JWT token.
        return sha1((string) $jwt);
    }
}
