<?php

namespace LittleApps\LittleJWT\Validation;

use Closure;

use Illuminate\Support\Traits\Macroable;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Contracts\Rule;
use LittleApps\LittleJWT\JWT\Rules;
use LittleApps\LittleJWT\JWK\JsonWebKey;

class Validator
{
    use Macroable;

    /**
     * Blacklist Manager
     *
     * @var \LittleApps\LittleJWT\Blacklist\BlacklistManager
     */
    protected $blacklistManager;

    /**
     * Default JWK to use for validating signatures.
     *
     * @var JsonWebKey
     */
    protected $jwk;

    /**
     * Rules to run before any other rules.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $rulesBefore;

    /**
     * Rules to run through JWT
     *
     * @var \Illuminate\Support\Collection
     */
    protected $rules;

    /**
     * Callbacks to call after rules are checked.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $after;

    /**
     * If true, the validation ends immediately when a rule fails.
     *
     * @var bool
     */
    protected $stopOnFailure;

    public function __construct(BlacklistManager $blacklistManager, JsonWebKey $jwk)
    {
        $this->blacklistManager = $blacklistManager;
        $this->jwk = $jwk;

        $this->rulesBefore = collect();
        $this->rules = collect();
        $this->after = collect();

        $this->stopOnFailure = true;
    }

    /**
     * Checks if the JWT has a valid signature.
     *
     * @param JsonWebKey|null $jwk JWK instance. If null, default JWK is used. (default: null)
     * @param bool $before If true, runs rule before others. (default: true)
     * @return $this
     */
    public function valid(JsonWebKey $jwk = null, $before = true)
    {
        $jwk = $jwk ?? $this->jwk;

        $rule = new Rules\ValidSignature($jwk);

        return $before ? $this->addRuleBefore($rule) : $this->addRule($rule);
    }

    /**
     * Checks if claim uses one of the specified algorithms.
     *
     * @param array $algorithms Algorithm keys to check for (HS256, RS256, etc.)
     * @param bool $inHeader
     * @return $this
     */
    public function algorithms(array $algorithms, $inHeader = true)
    {
        return $this->oneOf('alg', $algorithms, true, $inHeader);
    }

    /**
     * Checks that the claims date/time is in the past.
     *
     * @param string $key Claim key
     * @param int $leeway Leeway (in seconds) to allow before claims set date/time. (default: 0)
     * @param bool $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function past($key, $leeway = 0, $inHeader = false)
    {
        return $this->addRule(new Rules\Claims\Past($key, $leeway, $inHeader));
    }

    /**
     * Checks that the claim date/time is in the future.
     *
     * @param string $key Claim key
     * @param int $leeway Leeway (in seconds) to allow after claims set date/time. (default: 0)
     * @param bool $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function future($key, $leeway = 0, $inHeader = false)
    {
        return $this->addRule(new Rules\Claims\Future($key, $leeway, $inHeader));
    }

    /**
     * Checks that claims with keys exist in header or payload.
     *
     * @param iterable $keys Claim keys to check for.
     * @param bool $strict If true, JWT can ONLY contain the keys. (default: false)
     * @param bool $inHeader If true, checks claim in header. (default: false)
     * @param bool $before If true, runs rule before others. (default: true)
     * @return $this
     */
    public function contains(iterable $keys, $strict = false, $inHeader = false, $before = true)
    {
        $rule = new Rules\ContainsClaims($keys, $inHeader, $strict);

        return $before ? $this->addRuleBefore($rule) : $this->addRule($rule);
    }

    /**
     * Checks value of claim with key equals expected.
     *
     * @param string $key Claim key
     * @param mixed $expected Expected value.
     * @param bool $strict If true, performs type comparison. (default: true)
     * @param bool $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function equals($key, $expected, $strict = true, $inHeader = false)
    {
        return $this->addRule(new Rules\Claims\Equals($key, $expected, $strict, $inHeader));
    }

    /**
     * Securely checks value of claim with key equals expected.
     *
     * @param string $key Claim key
     * @param mixed $expected Expected value.
     * @param bool $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function secureEquals($key, $expected, $inHeader = false)
    {
        return $this->addRule(new Rules\Claims\SecureEquals($key, $expected, $inHeader));
    }

    /**
     * Checks value of claim is one of the expected values
     *
     * @param string $key Claim key
     * @param array $haystack Expected values
     * @param bool $strict If true, performs type comparison. (default: true)
     * @param bool $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function oneOf($key, array $haystack, $strict = true, $inHeader = false)
    {
        return $this->addRule(new Rules\Claims\OneOf($key, $haystack, $strict, $inHeader));
    }

    /**
     * Checks the JWT is allowed (not blacklisted).
     *
     * @param string $driver Blacklist driver to use. If null, default driver is used. (default: null)
     * @param bool $before If true, runs rule before others. (default: true)
     * @return $this
     */
    public function allowed($driver = null, $before = true)
    {
        $rule = new Rules\Allowed($this->blacklistManager->driver($driver));

        return $before ? $this->addRuleBefore($rule) : $this->addRule($rule);
    }

    /**
     * Adds callback that is called with JWT and returns true/false.
     *
     * @param callable(JWT $jwt):boolean $callback
     * @return $this
     */
    public function callback(callable $callback)
    {
        return $this->addRule(new Rules\Callback($callback));
    }

    /**
     * Adds callback that is called with claim value and returns true/false.
     *
     * @param string $key Claim key
     * @param callable(mixed $value, string $key, JWT $jwt):boolean $callback Callback that accepts claim value and returns true/false.
     * @param bool $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function claimCallback($key, callable $callback, $inHeader = false)
    {
        return $this->addRule(new Rules\Claims\Callback($key, $callback, $inHeader));
    }

    /**
     * Indicates whether validation should stop when a rule fails.
     *
     * @param bool $enabled If true, validation stops when first rule fails. (default: true)
     * @return $this
     */
    public function stopOnFailure($enabled = true)
    {
        $this->stopOnFailure = (bool) $enabled;

        return $this;
    }

    /**
     * Adds a callback to be called after validate is done.
     *
     * @param Closure $callback
     * @return $this
     */
    public function afterValidate(Closure $callback)
    {
        $this->after->push($callback);

        return $this;
    }

    /**
     * Adds a rule to be used on JWT before others.
     *
     * @param Rule $rule
     * @return $this
     */
    public function addRuleBefore(Rule $rule)
    {
        $this->rulesBefore->push($rule);

        return $this;
    }

    /**
     * Adds a rule to be used on JWT.
     *
     * @param Rule $rule
     * @return $this
     */
    public function addRule(Rule $rule)
    {
        $this->rules->push($rule);

        return $this;
    }

    /**
     * Adds rules to be used on JWT.
     *
     * @param iterable $rules
     * @return $this
     */
    public function addRules(iterable $rules)
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }

    /**
     * Gets rules to run before others.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRulesBefore()
    {
        return collect($this->rulesBefore);
    }

    /**
     * Gets rules to run.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRules()
    {
        return collect($this->rules);
    }

    /**
     * Gets callbacks to call after validation.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAfterValidation()
    {
        return collect($this->after);
    }

    /**
     * Gets if validation should stop when first rule fails
     *
     * @return bool
     */
    public function getStopOnFailure()
    {
        return $this->stopOnFailure;
    }

    /**
     * Gets the JWK associated with this Validator instance.
     *
     * @return \Jose\Component\Core\JWK
     */
    public function getJwk()
    {
        return $this->jwk;
    }
}
