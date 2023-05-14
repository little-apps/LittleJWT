<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Factories\JWTBuilder;

trait CreatesJWTBuilder
{
    /**
     * Creates JWT Builder
     *
     * @return JWTBuilder
     */
    public function createJWTBuilder()
    {
        return new JWTBuilder($this->sign());
    }
}
