<?php

namespace LittleApps\LittleJWT\Core\Concerns;

use LittleApps\LittleJWT\Factories\DefaultCallbackBuilder;

trait CreatesCallbackBuilder {
    /**
     * Gets the default callback builder.
     *
     * @return DefaultCallbackBuilder
     */
    protected function createCallbackBuilder()
    {
        return $this->app->make(DefaultCallbackBuilder::class);
    }
}
