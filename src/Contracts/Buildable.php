<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\Build\Builder;

/**
 * Buildable interface
 *
 * @deprecated 1.6.0 Deprecated in favor of invokable classes
 */
interface Buildable
{
    /**
     * Builds a JWT.
     *
     * @return void
     */
    public function build(Builder $builder);
}
