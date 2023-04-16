<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use Illuminate\Support\Carbon;

use Illuminate\Support\Str;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Buildable;

class DefaultBuildable
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke(Builder $builder)
    {
        $builder
            ->alg($this->config['alg'])
            ->iat(Carbon::now())
            ->nbf(Carbon::now())
            ->exp(Carbon::now()->addSeconds($this->config['ttl']))
            ->iss($this->config['iss'])
            ->jti((string) Str::uuid());

        foreach ((array) $this->config['aud'] as $aud) {
            $builder->aud($aud);
        }
    }
}
