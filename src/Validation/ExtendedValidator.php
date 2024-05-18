<?php

namespace LittleApps\LittleJWT\Validation;

use Illuminate\Contracts\Container\Container;
use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Contracts\BuildsValidatorRules;
use LittleApps\LittleJWT\Core\Concerns\CreatesCallbackBuilder;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\Rules;

class ExtendedValidator extends Validator implements BuildsValidatorRules
{
    use CreatesCallbackBuilder;

    const DEFAULTS_NONE = false;

    const DEFAULTS_BEFORE = 'before';

    const DEFAULTS_AFTER = 'after';

    protected $includeDefaults = self::DEFAULTS_BEFORE;

    protected $beforeValidatables = [];

    protected $afterValidatables = [];

    /**
     * Initializes the ExtendedValidator instance.
     */
    public function __construct(
        protected readonly Container $app,
        BlacklistManager $blacklistManager,
        JsonWebKey $jwk
    ) {
        parent::__construct($blacklistManager, $jwk);
    }

    /**
     * Includes default validator rules
     *
     * @param  bool  $after  If true, default validation rules are added at the end.
     * @return $this
     */
    public function withDefaults($after = false): static
    {
        $this->includeDefaults = (bool) $after ? static::DEFAULTS_AFTER : static::DEFAULTS_BEFORE;

        return $this;
    }

    /**
     * Doesn't include default validator rules
     *
     * @return $this
     */
    public function withoutDefaults(): static
    {
        $this->includeDefaults = false;

        return $this;
    }

    /**
     * Includes validatable
     *
     * @param  callable  $validatable
     * @param  bool  $after  If true, added at the end.
     */
    public function with($validatable, bool $after = false): static
    {
        if (! $after) {
            array_unshift($this->beforeValidatables, $validatable);
        } else {
            array_push($this->afterValidatables, $validatable);
        }

        return $this;
    }

    /**
     * Excludes validatable
     *
     * @param  callable  $validatable
     */
    public function without($validatable, bool $after = false): static
    {
        if (! $after) {
            $this->beforeValidatables = array_filter($this->beforeValidatables, fn ($callback) => $callback !== $validatable);
        } else {
            $this->afterValidatables = array_filter($this->afterValidatables, fn ($callback) => $callback !== $validatable);
        }

        return $this;
    }

    /**
     * Gets stack of validatable callbacks.
     *
     * @return list<callable>
     */
    public function getStack(): array
    {
        $stack = [
            ...$this->beforeValidatables,
            function (Validator $validator) {
                $this->copyRulesTo($validator);
            },
            ...$this->afterValidatables,
        ];

        if ($this->includeDefaults === static::DEFAULTS_BEFORE) {
            array_unshift($stack, $this->createCallbackBuilder()->createValidatableCallback());
        } elseif ($this->includeDefaults === static::DEFAULTS_AFTER) {
            array_push($stack, $this->createCallbackBuilder()->createValidatableCallback());
        }

        return $stack;
    }
}
