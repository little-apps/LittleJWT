<?php

namespace LittleApps\LittleJWT\Verify;

use Closure;

use LittleApps\LittleJWT\Contracts\Rule;
use LittleApps\LittleJWT\JWT\Rules;
use LittleApps\LittleJWT\Blacklist\BlacklistManager;

use Illuminate\Support\Traits\Macroable;

use Jose\Component\Core\JWK;

class Verifier {
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
     * @var \Jose\Component\Core\JWK
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

    public function __construct(BlacklistManager $blacklistManager, JWK $jwk) {
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
     * @param JWK|null $jwk JWK instance. If null, default JWK is used. (default: null)
     * @param boolean $before If true, runs rule before others. (default: true)
     * @return $this
     */
    public function valid(JWK $jwk = null, $before = true) {
        $jwk = $jwk ?? $this->jwk;

        $rule = new Rules\ValidSignature($jwk);

        return $before ? $this->addRuleBefore($rule) : $this->addRule($rule);
    }

    /**
     * Checks if claim uses one of the specified algorithms.
     *
     * @param array $algorithms Algorithm keys to check for (HS256, RS256, etc.)
     * @param boolean $inHeader
     * @return $this
     */
    public function algorithms(array $algorithms, $inHeader = true) {
        return $this->oneOf('alg', $algorithms, true, $inHeader);
    }

    /**
     * Checks that the current date/time is before the claims date/time.
     *
     * @param string $key Claim key
     * @param int $leeway Leeway (in seconds) to allow after claims set date/time. (default: 0)
     * @param boolean $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function before($key, $leeway = 0, $inHeader = false) {
        return $this->addRule(new Rules\Claims\Before($key, $leeway, $inHeader));
    }

    /**
     * Checks that the current date/time is after the claims date/time.
     *
     * @param string $key Claim key
     * @param int $leeway Leeway (in seconds) to allow before claims set date/time. (default: 0)
     * @param boolean $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function after($key, $leeway = 0, $inHeader = false) {
        return $this->addRule(new Rules\Claims\After($key, $leeway, $inHeader));
    }

    /**
     * Checks that claims with keys exist in header or payload.
     *
     * @param iterable $keys Claim keys to check for.
     * @param boolean $strict If true, JWT can ONLY contain the keys. (default: false)
     * @param boolean $inHeader If true, checks claim in header. (default: false)
     * @param boolean $before If true, runs rule before others. (default: true)
     * @return $this
     */
    public function contains(iterable $keys, $strict = false, $inHeader = false, $before = true) {
        $rule = new Rules\ContainsClaims($keys, $inHeader, $strict);

        return $before ? $this->addRuleBefore($rule) : $this->addRule($rule);
    }

    /**
     * Checks value of claim with key equals expected.
     *
     * @param string $key Claim key
     * @param mixed $expected Expected value.
     * @param boolean $strict If true, performs type comparison. (default: true)
     * @param boolean $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function equals($key, $expected, $strict = true, $inHeader = false) {
        return $this->addRule(new Rules\Claims\Equals($key, $expected, $strict, $inHeader));
    }

    /**
     * Securely checks value of claim with key equals expected.
     *
     * @param string $key Claim key
     * @param mixed $expected Expected value.
     * @param boolean $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function secureEquals($key, $expected, $inHeader = false) {
        return $this->addRule(new Rules\Claims\SecureEquals($key, $expected, $inHeader));
    }

    /**
     * Checks value of claim is one of the expected values
     *
     * @param string $key Claim key
     * @param mixed $haystack Expected values
     * @param boolean $strict If true, performs type comparison. (default: true)
     * @param boolean $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function oneOf($key, array $haystack, $strict = true, $inHeader = false) {
        return $this->addRule(new Rules\Claims\OneOf($key, $haystack, $strict, $inHeader));
    }

    /**
     * Checks the JWT is allowed (not blacklisted).
     *
     * @param string $driver Blacklist driver to use. If null, default driver is used. (default: null)
     * @param bool $before If true, runs rule before others. (default: true)
     * @return $this
     */
    public function allowed($driver = null, $before = true) {
        $rule = new Rules\Allowed($this->blacklistManager->driver($driver));

        return $before ? $this->addRuleBefore($rule) : $this->addRule($rule);
    }

    /**
     * Adds callback that is called with JWT and returns true/false.
     *
     * @param Closure $callback
     * @return $this
     */
    public function callback(Closure $callback) {
        return $this->addRule(new Rules\Callback($callback));
    }

    /**
     * Adds callback that is called with claim value and returns true/false.
     *
     * @param string $key Claim key
     * @param Closure $callback Callback that accepts claim value and returns true/false.
     * @param boolean $inHeader If true, checks claim in header. (default: false)
     * @return $this
     */
    public function claimCallback($key, Closure $callback, $inHeader = false) {
        return $this->addRule(new Rules\Claims\Callback($key, $callback, $inHeader));
    }

    /**
     * Indicates whether validation should stop when a rule fails.
     *
     * @param boolean $enabled If true, validation stops when first rule fails. (default: true)
     * @return $this
     */
    public function stopOnFailure($enabled = true) {
        $this->stopOnFailure = (bool) $enabled;

        return $this;
    }

    /**
     * Adds a callback to be called after verify is done.
     *
     * @param Closure $callback
     * @return $this
     */
    public function afterVerify(Closure $callback) {
        $this->after->push($callback);

        return $this;
    }

    /**
     * Adds a rule to be used on JWT before others.
     *
     * @param Rule $rule
     * @return $this
     */
    public function addRuleBefore(Rule $rule) {
        $this->rulesBefore->push($rule);

        return $this;
    }

    /**
     * Adds a rule to be used on JWT.
     *
     * @param Rule $rule
     * @return $this
     */
    public function addRule(Rule $rule) {
        $this->rules->push($rule);

        return $this;
    }

    /**
     * Adds rules to be used on JWT.
     *
     * @param iterable $rules
     * @return $this
     */
    public function addRules(iterable $rules) {
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
    public function getRulesBefore() {
        return collect($this->rulesBefore);
    }

    /**
     * Gets rules to run.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRules() {
        return collect($this->rules);
    }

    /**
     * Gets callbacks to call after verify.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAfterVerify() {
        return collect($this->after);
    }

    /**
     * Gets if verification should stop when first rule fails
     *
     * @return bool
     */
    public function getStopOnFailure() {
        return $this->stopOnFailure;
    }

    /**
     * Gets the JWK associated with this verifier instance.
     *
     * @return \Jose\Component\Core\JWK
     */
    public function getJwk() {
        return $this->jwk;
    }
}
