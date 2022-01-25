<?php

namespace LittleApps\LittleJWT\Validation\Validators;

use Illuminate\Contracts\Foundation\Application;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

class DefaultValidator implements Validatable
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

    public function validate(Validator $validator)
    {
        $validator
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
