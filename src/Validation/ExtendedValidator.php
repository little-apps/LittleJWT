<?php

namespace LittleApps\LittleJWT\Validation;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Contracts\BuildsValidatorRules;
use LittleApps\LittleJWT\Contracts\Rule;
use LittleApps\LittleJWT\Core\Concerns\CreatesCallbackBuilder;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\JsonWebToken;
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
     *
     * @param Container $app
     * @param BlacklistManager $blacklistManager
     * @param JsonWebKey $jwk
     */
    public function __construct(
        protected readonly Container $app,
        BlacklistManager $blacklistManager,
        JsonWebKey $jwk
    )
    {
        parent::__construct($blacklistManager, $jwk);
    }

    /**
     * Includes default validator rules
     *
     * @param boolean $after If true, default validation rules are added at the end.
     * @return $this
     */
    public function withDefaults($after = false): static {
        $this->includeDefaults = (bool) $after ? static::DEFAULTS_AFTER : static::DEFAULTS_BEFORE;

        return $this;
    }

    /**
     * Doesn't include default validator rules
     *
     * @return $this
     */
    public function withoutDefaults(): static {
        $this->includeDefaults = false;

        return $this;
    }

    /**
     * Gets stack of validatable callbacks.
     *
     * @return list<callable>
     */
    public function getStack(): array {
        $stack = [
            ...$this->beforeValidatables,
            function (Validator $validator) {
                $this->copyRulesTo($validator);
            },
            ...$this->afterValidatables
        ];

        if ($this->includeDefaults === static::DEFAULTS_BEFORE) {
            array_unshift($stack, $this->createCallbackBuilder()->createValidatableCallback());
        } else if ($this->includeDefaults === static::DEFAULTS_AFTER) {
            array_push($stack, $this->createCallbackBuilder()->createValidatableCallback());
        }

        return $stack;
    }
}
