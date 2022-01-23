<?php

namespace LittleApps\LittleJWT\Contracts;

use LittleApps\LittleJWT\Build\Builder;

interface Buildable {
    /**
     * Builds a JWT.
     *
     * @param Builder $builder
     * @return void
     */
    public function build(Builder $builder);
}
