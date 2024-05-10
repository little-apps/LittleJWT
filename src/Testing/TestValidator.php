<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Traits\Macroable;
use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Concerns\JWTHelpers;
use LittleApps\LittleJWT\Contracts\BuildsValidatorRules;
use LittleApps\LittleJWT\Contracts\Rule;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\Rules;
use LittleApps\LittleJWT\Validation\ExtendedValidator;
use PHPUnit\Framework\Assert as PHPUnit;

class TestValidator extends ExtendedValidator implements BuildsValidatorRules
{
    use JWTHelpers;
    use Macroable;

    /**
     * Holds value of whether to assert that the JWT passes all rules.
     *
     * @var bool
     */
    protected $assertPasses;

    /**
     * Holds value of whether to assert that the JWT doesn't pass all rules.
     *
     * @var bool
     */
    protected $assertFails;

    /**
     * The expected error count.
     *
     * @var int|false
     */
    protected $expectedErrorCount;

    /**
     * The unexpected error count.
     *
     * @var int|false
     */
    protected $unexpectedErrorCount;

    /**
     * The expected error keys.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $expectedErrorKeys;

    /**
     * The unexpected error keys.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $unexpectedErrorKeys;

    /**
     * Constructor for TestValidator
     */
    public function __construct(Application $app, BlacklistManager $blacklistManager, JsonWebKey $jwk)
    {
        parent::__construct($app, $blacklistManager, $jwk);

        $this->assertPasses = false;
        $this->assertFails = false;
        $this->expectedErrorCount = false;
        $this->unexpectedErrorCount = false;
        $this->expectedErrorKeys = collect();
        $this->unexpectedErrorKeys = collect();

        $this->afterValidate($this->getAfterValidationCallback());
    }

    /**
     * Sets whether to assert that the JWT rules pass.
     *
     * @param  bool  $enabled
     * @return $this
     */
    public function assertPasses($enabled = true)
    {
        $this->assertPasses = (bool) $enabled;

        return $this;
    }

    /**
     * Sets whether to assert that the JWT rules fail.
     *
     * @param  bool  $enabled
     * @return $this
     */
    public function assertFails($enabled = true)
    {
        $this->assertFails = (bool) $enabled;

        return $this;
    }

    /**
     * Asserts that there were so many errors from the JWT validation.
     *
     * @param  int|false  $count  Expected count or false to disable assert (default: false)
     * @return $this
     */
    public function assertErrorCount($count = false)
    {
        $this->expectedErrorCount = $count;

        return $this;
    }

    /**
     * Asserts that there weren't so many errors from the JWT validation.
     *
     * @param  int|false  $count  Error count or false to disable assert (default: false)
     * @return $this
     */
    public function assertNotErrorCount($count = false)
    {
        $this->unexpectedErrorCount = $count;

        return $this;
    }

    /**
     * Asserts that an error with key exists.
     *
     * @param  string  $key
     * @return $this
     */
    public function assertErrorKeyExists($key)
    {
        $this->expectedErrorKeys->push($key);

        return $this;
    }

    /**
     * Asserts that an error with key doesn't exist.
     *
     * @param  string  $key
     * @return $this
     */
    public function assertErrorKeyDoesntExist($key)
    {
        $this->unexpectedErrorKeys->push($key);

        return $this;
    }

    /**
     * Asserts that a rule passes.
     *
     * @param  string  $message
     * @return $this
     */
    public function assertRulePasses(Rule $rule, $message = '')
    {
        $this->addRule(new TestRule($rule, true, $message));

        return $this;
    }

    /**
     * Asserts that a rule fails.
     *
     * @param  string  $message
     * @return $this
     */
    public function assertRuleFails(Rule $rule, $message = '')
    {
        $this->addRule(new TestRule($rule, false, $message));

        return $this;
    }

    /**
     * Asserts the JWT is for the subject model
     *
     * @param  mixed  $model  Class name or object
     * @return $this
     */
    public function assertSubjectModel($model)
    {
        return $this->assertRulePasses(
            new Rules\Claims\SecureEquals('prv', $this->hashSubjectModel($model)),
            sprintf('Failed asserting that subject model is instance of "%s"', is_string($model) ? $model : get_class($model))
        );
    }

    /**
     * Asserts the JWT is not for the subject model
     *
     * @param  mixed  $model  Class name or object
     * @return $this
     */
    public function assertNotSubjectModel($model)
    {
        return $this->assertRuleFails(
            new Rules\Claims\SecureEquals('prv', $this->hashSubjectModel($model)),
            sprintf('Failed asserting that subject model is not an instance of "%s"', is_string($model) ? $model : get_class($model))
        );
    }

    /**
     * Asserts the JWT payload/header claim is in the past.
     *
     * @param  string  $key  Claim key
     * @param  int  $leeway  Leeway (in seconds) to allow before claims set date/time. (default: 0)
     * @param  bool  $inHeader  If true, checks claim in header. (default: false)
     * @return $this
     */
    public function assertPastPasses($key, $leeway = 0, $inHeader = false)
    {
        return $this->assertRulePasses(
            new Rules\Claims\Past($key, $leeway, $inHeader),
            'Failed asserting that the JWT claim is in the past.'
        );
    }

    /**
     * Asserts the JWT payload/header claim is not in the past.
     *
     * @param  string  $key  Claim key
     * @param  int  $leeway  Leeway (in seconds) to allow before claims set date/time. (default: 0)
     * @param  bool  $inHeader  If true, checks claim in header. (default: false)
     * @return $this
     */
    public function assertPastFails($key, $leeway = 0, $inHeader = false)
    {
        return $this->assertRuleFails(
            new Rules\Claims\Past($key, $leeway, $inHeader),
            'Failed asserting that the JWT claim is in the past.'
        );
    }

    /**
     * Asserts the JWT payload/header claim is in the future.
     *
     * @param  string  $key  Claim key
     * @param  int  $leeway  Leeway (in seconds) to allow before claims set date/time. (default: 0)
     * @param  bool  $inHeader  If true, checks claim in header. (default: false)
     * @return $this
     */
    public function assertFuturePasses($key, $leeway = 0, $inHeader = false)
    {
        return $this->assertRulePasses(
            new Rules\Claims\Future($key, $leeway, $inHeader),
            'Failed asserting that the JWT claim is in the future.'
        );
    }

    /**
     * Asserts the JWT payload/header claim is not in the future.
     *
     * @param  string  $key  Claim key
     * @param  int  $leeway  Leeway (in seconds) to allow before claims set date/time. (default: 0)
     * @param  bool  $inHeader  If true, checks claim in header. (default: false)
     * @return $this
     */
    public function assertFutureFails($key, $leeway = 0, $inHeader = false)
    {
        return $this->assertRuleFails(
            new Rules\Claims\Future($key, $leeway, $inHeader),
            'Failed asserting that the JWT claim is in the future.'
        );
    }

    /**
     * Asserts a claim matches a value
     *
     * @param  string  $claimKey
     * @param  mixed  $value
     * @param  bool  $strict
     * @param  bool  $inHeader
     * @return $this
     */
    public function assertClaimMatches($claimKey, $value, $strict = false, $inHeader = false)
    {
        return $this->assertRulePasses(new Rules\Claims\Equals($claimKey, $value, $strict, $inHeader));
    }

    /**
     * Asserts a claim matches a value
     *
     * @param  string  $claimKey
     * @param  mixed  $value
     * @param  bool  $strict
     * @param  bool  $inHeader
     * @return $this
     */
    public function assertClaimDoesntMatch($claimKey, $value, $strict = false, $inHeader = false)
    {
        return $this->assertRuleFails(new Rules\Claims\Equals($claimKey, $value, $strict, $inHeader));
    }

    /**
     * Asserts a claim exists in payload.
     *
     * @param  mixed  $expected  Expected claim keys as an array or multiple parameters.
     * @param  bool  $strict  If true, only expected claims can exist.
     * @param  bool  $inHeader  If true, asserts the claim keys are in the header.
     * @return $this
     */
    public function assertClaimsExists($expected, $strict = false, $inHeader = false)
    {
        $expected = is_array($expected) ? $expected : func_get_args();

        return $this->assertRulePasses(new Rules\ContainsClaims($expected, $inHeader, $strict));
    }

    /**
     * Asserts a claim exists in payload.
     *
     * @param  mixed  $expected  Expected claim keys as an array or multiple parameters.
     * @param  bool  $strict  If true, only expected claims can't exist.
     * @param  bool  $inHeader  If true, asserts the claim keys are not in the header.
     * @return $this
     */
    public function assertClaimsDoesntExist($expected, $strict = false, $inHeader = false)
    {
        $expected = is_array($expected) ? $expected : func_get_args();

        return $this->assertRuleFails(new Rules\ContainsClaims($expected, $inHeader, $strict));
    }

    /**
     * Asserts a claim has a valid signature.
     *
     * @return $this
     */
    public function assertValidSignature(?JsonWebKey $jwk = null)
    {
        return $this->assertRulePasses(
            new Rules\ValidSignature($jwk ?? $this->getJwk()),
            'Failed asserting that the JWT has a valid signature.'
        );
    }

    /**
     * Asserts a JWT has a invalid signature.
     *
     * @return $this
     */
    public function assertInvalidSignature(?JsonWebKey $jwk = null)
    {
        return $this->assertRuleFails(
            new Rules\ValidSignature($jwk ?? $this->getJwk()),
            'Failed asserting that the JWT has a invalid signature.'
        );
    }

    /**
     * Asserts a JWT is allowed (not blacklisted).
     *
     * @param  string  $driver  Blacklist driver to use. If null, default is used. (default: null)
     * @return $this
     */
    public function assertAllowed(?string $driver = null)
    {
        return $this->assertRulePasses(
            new Rules\Allowed($this->app->make(BlacklistManager::class)->driver($driver)),
            'Failed asserting that the JWT is not blacklisted.'
        );
    }

    /**
     * Asserts a JWT is allowed (not blacklisted).
     *
     * @param  string  $driver  Blacklist driver to use. If null, default is used. (default: null)
     * @return $this
     */
    public function assertNotAllowed(?string $driver = null)
    {
        return $this->assertRuleFails(
            new Rules\Allowed($this->app->make(BlacklistManager::class)->driver($driver)),
            'Failed asserting that the JWT is blacklisted.'
        );
    }

    /**
     * Asserts a custom check passes.
     *
     * @param  callable(JsonWebToken $jwt):bool  $callback
     * @return $this
     */
    public function assertCustomPasses(callable $callback)
    {
        return $this->assertRulePasses(
            new Rules\Callback($callback),
            'Failed asserting that the custom check passes.'
        );
    }

    /**
     * Asserts a custom check fails.
     *
     * @param  callable(JsonWebToken $jwt):bool  $callback
     * @return $this
     */
    public function assertCustomFails(callable $callback)
    {
        return $this->assertRuleFails(
            new Rules\Callback($callback),
            'Failed asserting that the custom check fails.'
        );
    }

    /**
     * Asserts a custom claim check passes.
     *
     * @param  string  $key  Claim key
     * @param  callable(mixed $value, string $key, JsonWebToken $jwt):bool  $callback  Callback that accepts claim value and returns true/false.
     * @param  bool  $inHeader  If true, checks claim in header. (default: false)
     * @return $this
     */
    public function assertCustomClaimPasses($key, callable $callback, $inHeader = false)
    {
        return $this->assertRulePasses(
            new Rules\Claims\Callback($key, $callback, $inHeader),
            'Failed asserting that the custom check passes.'
        );
    }

    /**
     * Asserts a custom claim check fails.
     *
     * @param  string  $key  Claim key
     * @param  callable(mixed $value, string $key, JsonWebToken $jwt):bool  $callback  Callback that accepts claim value and returns true/false.
     * @param  bool  $inHeader  If true, checks claim in header. (default: false)
     * @return $this
     */
    public function assertCustomClaimFails($key, callable $callback, $inHeader = false)
    {
        return $this->assertRuleFails(
            new Rules\Claims\Callback($key, $callback, $inHeader),
            'Failed asserting that the custom check fails.'
        );
    }

    /**
     * Gets callback to run after validation is done.
     *
     * @return \Closure
     */
    protected function getAfterValidationCallback()
    {
        return function ($passes, $errors) {
            if ($this->assertPasses) {
                PHPUnit::assertTrue($passes, 'JWT validation did not pass.');
            }

            if ($this->assertFails) {
                PHPUnit::assertFalse($passes, 'JWT validation passed.');
            }

            if ($this->expectedErrorCount !== false) {
                PHPUnit::assertEquals($this->expectedErrorCount, $errors->count(), 'The error count does not match.');
            }

            if ($this->unexpectedErrorCount !== false) {
                PHPUnit::assertNotEquals($this->unexpectedErrorCount, $errors->count(), 'The error count matches.');
            }

            if ($this->expectedErrorKeys->count() > 0) {
                foreach ($this->expectedErrorKeys as $key) {
                    PHPUnit::assertTrue($errors->has($key), sprintf('No errors for key \'%s\' exist.', $key));
                }
            }

            if ($this->unexpectedErrorKeys->count() > 0) {
                foreach ($this->unexpectedErrorKeys as $key) {
                    PHPUnit::assertFalse($errors->has($key), sprintf('Errors for key \'%s\' exist.', $key));
                }
            }
        };
    }
}
