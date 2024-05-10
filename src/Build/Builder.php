<?php

namespace LittleApps\LittleJWT\Build;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use LittleApps\LittleJWT\Contracts\BuildsJWTClaims;
use LittleApps\LittleJWT\Core\Concerns\CreatesCallbackBuilder;
use LittleApps\LittleJWT\JWT\ClaimManagers;

final class Builder extends Options implements BuildsJWTClaims
{
    const DEFAULTS_NONE = false;

    const DEFAULTS_BEFORE = 'before';

    const DEFAULTS_AFTER = 'after';

    use Macroable {
        __call as macroCall;
    }
    use ForwardsCalls;
    use CreatesCallbackBuilder;

    /**
     * If and when to include default buildables.
     *
     * @var string|false
     */
    protected $includeDefaults = self::DEFAULTS_BEFORE;

    /**
     * Buildables to run before options.
     *
     * @var list<callable>
     */
    protected $beforeBuildables = [];

    /**
     * Buildables to run after options.
     *
     * @var list<callable>
     */
    protected $afterBuildables = [];

    /**
     * Initializes Builder instance
     *
     * @param Container $app
     */
    public function __construct(
        protected readonly Container $app
    ) {
        parent::__construct();
    }

    /**
     * Includes default claims
     *
     * @param bool $after If true, default claims are added at the end.
     * @return $this
     */
    public function withDefaults($after = false)
    {
        $this->includeDefaults = (bool) $after ? static::DEFAULTS_AFTER : static::DEFAULTS_BEFORE;

        return $this;
    }

    /**
     * Doesn't include default claims
     *
     * @return $this
     */
    public function withoutDefaults()
    {
        $this->includeDefaults = static::DEFAULTS_NONE;

        return $this;
    }

    /**
     * Gets the JWT claims.
     *
     * @return ClaimManagers
     */
    public function getClaimManagers(): ClaimManagers
    {
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
    protected function getBeforeCallbacks(): array
    {
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
    protected function getClaimManagersFromCallbacks(array $callbacks): ClaimManagers
    {
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
    protected function getAfterCallbacks(): array
    {
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

    public static function defaults()
    {
        return function (Builder $builder) {
            $builder->withDefaults();
        };
    }
}
