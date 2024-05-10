<?php

namespace LittleApps\LittleJWT\Contracts;

interface BuildsValidatorRules
{
    /**
     * Gets rules to run before others.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRulesBefore();

    /**
     * Gets rules to run.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRules();
}
