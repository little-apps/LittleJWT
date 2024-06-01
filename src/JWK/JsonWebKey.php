<?php

namespace LittleApps\LittleJWT\JWK;

use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Exceptions\HashAlgorithmNotFoundException;
use LittleApps\LittleJWT\Factories\AlgorithmBuilder;

class JsonWebKey extends JWK
{
    public function __construct(array $values)
    {
        parent::__construct($values);
    }

    /**
     * Gets hash algorithm instance based on 'alg' value for JWK.
     *
     * @return \Jose\Component\Core\Algorithm
     *
     * @throws HashAlgorithmNotFoundException Thrown if algorithm could not be determined.
     */
    public function algorithm()
    {
        $alg = $this->has('alg') ? strtoupper($this->get('alg')) : null;

        if (is_null($alg)) {
            throw new HashAlgorithmNotFoundException('Json Web Key doesn\'t have algorithm set.');
        }

        return AlgorithmBuilder::build($alg);
    }
}
