<?php

namespace LittleApps\LittleJWT\Factories;

use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWK\JWKValidator;
use LittleApps\LittleJWT\LittleJWT;

class LittleJWTBuilder
{
    /**
     * JWKValidator to use
     */
    protected ?JWKValidator $jwkValidator;

    /**
     * Initializes LittleJWTBuilder instance
     *
     * @param  JsonWebKey  $jwk  JWK to use with LittleJWT instance.
     */
    public function __construct(
        protected readonly JsonWebKey $jwk
    ) {}

    /**
     * Specifies JWKValidator to use before building LittleJWT.
     *
     * @return $this
     */
    public function withJwkValidator(JWKValidator $jwkValidator)
    {
        $this->jwkValidator = $jwkValidator;

        return $this;
    }

    /**
     * Specifies to not use JWKValidator before building LittleJWT.
     *
     * @return $this
     */
    public function withoutJwkValidator()
    {
        $this->jwkValidator = null;

        return $this;
    }

    /**
     * Builds LittleJWT instance
     */
    public function build(): LittleJWT
    {
        $jwk = isset($this->jwkValidator) ? $this->jwkValidator->__invoke($this->jwk) : $this->jwk;

        return new LittleJWT(app(), $jwk);
    }
}
