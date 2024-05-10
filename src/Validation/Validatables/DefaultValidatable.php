<?php

namespace LittleApps\LittleJWT\Validation\Validatables;

use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

/**
 * The default validatable for Little JWT.
 *
 * @see https://docs.getlittlejwt.com/en/validatables#default-validatable
 */
class DefaultValidatable
{
    /**
     * Default validatable configuration options.
     *
     * @var array
     */
    protected $config;

    /**
     * Intializes default validatable.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Applies validator rules.
     *
     * @return void
     */
    public function __invoke(Validator $validator)
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
            ->arrayEquals('aud', (array) $this->config['aud'])
            ->equals('iss', $this->config['iss']);
    }
}
