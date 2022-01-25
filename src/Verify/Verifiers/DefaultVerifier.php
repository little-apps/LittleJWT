<?php

namespace LittleApps\LittleJWT\Verify\Verifiers;

use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\Contracts\Validatable;

use LittleApps\LittleJWT\Verify\Validator;

class DefaultVerifier implements Validatable
{
    protected $app;

    protected $config;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;

        $this->config = [
            'alg' => $config['claims']['alg'],
            'required' => $config['claims']['required'],
            'leeway' => $config['claims']['leeway'],
            'aud' => $config['claims']['aud'],
            'iss' => $config['claims']['iss'],
        ];
    }

    public function verify(Validator $verifier)
    {
        $verifier
            ->algorithms([$this->config['alg']])
            ->contains($this->config['required']['header'], false, true)
            ->contains($this->config['required']['payload'])
            ->valid()
            ->allowed()
            ->before('exp', $this->config['leeway'])
            ->after('nbf', $this->config['leeway'])
            ->after('iat')
            ->equals('aud', $this->config['aud'])
            ->equals('iss', $this->config['iss']);
    }
}
