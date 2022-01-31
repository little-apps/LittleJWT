<?php

namespace LittleApps\LittleJWT\Build\Builders;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Carbon;

use Illuminate\Support\Str;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Contracts\Buildable;

class DefaultBuilder implements Buildable
{
    protected $app;

    protected $config;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function build(Builder $builder)
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
