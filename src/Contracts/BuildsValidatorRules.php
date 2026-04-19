<?php

namespace LittleApps\LittleJWT\Contracts;

use Illuminate\Support\Collection;

interface BuildsValidatorRules
{
    /**
     * Gets rules to run before others.
     *
     * @return Collection
     */
    public function getRulesBefore();

    /**
     * Gets rules to run.
     *
     * @return Collection
     */
    public function getRules();
}
