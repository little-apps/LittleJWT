<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LittleApps\LittleJWT\Build\Options;

class DefaultBuildable
{
    /**
     * Configuration for buildable.
     *
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Builds JWT with default claims.
     *
     * @return void
     */
    public function __invoke(Options $options)
    {
        $options
            ->alg($this->config['alg'])
            ->iat(Carbon::now())
            ->nbf(Carbon::now())
            ->exp(Carbon::now()->addSeconds($this->config['ttl']))
            ->iss($this->config['iss'])
            ->jti((string) Str::uuid())
            ->aud((array) $this->config['aud']);
    }
}
