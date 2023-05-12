<?php

namespace LittleApps\LittleJWT\Factories;

use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\Mutate\Mutatables\DefaultMutatable;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Validation\Validator;

class DefaultCallbackBuilder {
    /**
     * Application container
     *
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Gets the default buildable callback.
     *
     * @return callable(Builder): void
     */
    public function createBuildableCallback() {
        $alias = sprintf('littlejwt.buildables.%s', $this->app->config->get('littlejwt.defaults.buildable'));

        return $this->app->make($alias);
    }

    public function createMutatableCallback() {
        $config = $this->app->config->get('littlejwt.builder.mutators');

        return new DefaultMutatable($config);
    }

    /**
     * Creates the default Validatable callback.
     *
     * @return callable(Validator): void
     */
    public function createValidatableCallback() {
        $validatable = ValidatableBuilder::resolve($this->app->config->get('littlejwt.defaults.validatable'));

        return $validatable;
    }
}
