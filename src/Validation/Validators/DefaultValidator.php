<?php

namespace LittleApps\LittleJWT\Validation\Validators;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

class DefaultValidator implements Validatable
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function validate(Validator $validator)
    {
        $validator
            ->algorithms([$this->config['alg']])
            ->contains($this->config['required']['header'], false, true)
            ->contains($this->config['required']['payload'])
            ->valid()
            ->allowed()
            ->future('exp', $this->config['leeway'])
            ->past('nbf', $this->config['leeway'])
            ->past('iat')
            ->equals('aud', $this->config['aud'])
            ->equals('iss', $this->config['iss']);
    }
}
