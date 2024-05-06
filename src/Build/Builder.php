<?php

namespace LittleApps\LittleJWT\Build;

use BadMethodCallException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use LittleApps\LittleJWT\Contracts\BuildsJWTClaims;
use Illuminate\Support\Traits\Macroable;
use LittleApps\LittleJWT\Core\Concerns\CreatesCallbackBuilder;
use LittleApps\LittleJWT\Factories\ClaimManagerBuilder;
use LittleApps\LittleJWT\JWT\ClaimManager;
use LittleApps\LittleJWT\JWT\ClaimManagers;
use LittleApps\LittleJWT\JWT\ImmutableClaimManager;

final class Builder extends Options implements BuildsJWTClaims
{
    const DEFAULTS_BEFORE = 'before';
    const DEFAULTS_AFTER = 'after';

    use Macroable {
        __call as macroCall;
    }
    use ForwardsCalls;
    use CreatesCallbackBuilder;

    protected $includeDefaults = Builder::DEFAULTS_BEFORE;

    protected $beforeBuildables = [];
    protected $afterBuildables = [];

    public function __construct(
        protected readonly Container $app
    )
    {
        parent::__construct();
    }

    /**
     * Includes default claims
     *
     * @param boolean $after If true, default claims are added at the end.
     * @return $this
     */
    public function withDefaults($after = false) {
        $this->includeDefaults = (bool) $after ? static::DEFAULTS_AFTER : static::DEFAULTS_BEFORE;

        return $this;
    }

    /**
     * Doesn't include default claims
     *
     * @return $this
     */
    public function withoutDefaults() {
        $this->includeDefaults = false;

        return $this;
    }

    /**
     * Gets the JWT claims.
     *
     * @return ClaimManagers
     */
    public function getClaimManagers(): ClaimManagers {
        $claimManagers = [
            $this->getClaimManagersFromCallbacks($this->getBeforeCallbacks()),
            parent::getClaimManagers(),
            $this->getClaimManagersFromCallbacks($this->getAfterCallbacks()),
        ];

        return ClaimManagers::merge(...$claimManagers);
    }

    /**
     * Gets callbacks to run before other claims.
     *
     * @return list<callable>
     */
    protected function getBeforeCallbacks(): array {
        return
            $this->includeDefaults === static::DEFAULTS_BEFORE ?
                [$this->createCallbackBuilder()->createBuildableCallback(), ...$this->beforeBuildables] :
                $this->beforeBuildables;
    }

    /**
     * Gets claim managers from callbacks
     *
     * @param array $callbacks
     * @return ClaimManagers
     */
    protected function getClaimManagersFromCallbacks(array $callbacks): ClaimManagers {
        $options = new Options();

        foreach ($callbacks as $callback) {
            $callback($options);
        }

        return $options->getClaimManagers();
    }

    /**
     * Gets callbacks to run after other claims.
     *
     * @return list<callable>
     */
    protected function getAfterCallbacks(): array {
        return
            $this->includeDefaults === static::DEFAULTS_AFTER ?
                [...$this->afterBuildables, $this->createCallbackBuilder()->createBuildableCallback()] :
                $this->afterBuildables;
    }

    /**
     * Passes methods to macro or parent class.
     * Note: The magic method from the parent is not inherited, so this needs to be specified.
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    public function __call($name, $parameters)
    {
        if (static::hasMacro($name)) {
            return $this->macroCall($name, $parameters);
        }

        return parent::__call($name, $parameters);
    }

    public static function defaults() {
        return function (Builder $builder) {
            $builder->withDefaults();
        };
    }
}
