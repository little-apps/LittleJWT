<?php

namespace LittleApps\LittleJWT\Validation;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\MessageBag;
use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Concerns\PassableThru;
use LittleApps\LittleJWT\Contracts\BuildsValidatorRules;
use LittleApps\LittleJWT\Contracts\Rule;
use LittleApps\LittleJWT\Exceptions\RuleFailedException;
use LittleApps\LittleJWT\JWK\JsonWebKey;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class Valid
{
    use PassableThru;

    /**
     * Application container
     */
    protected readonly Application $app;

    /**
     * JWT to validate
     */
    protected readonly JsonWebToken $jwt;

    /**
     * JSON Web Key to verify signature with
     */
    protected readonly JsonWebKey $jwk;

    /**
     * Any errors that occurred.
     *
     * @var MessageBag
     */
    protected $errors;

    /**
     * The result of the last validation (or null if validation hasn't been done).
     *
     * @var bool|null
     */
    protected $lastRunResult;

    /**
     * Initializes a Valid instance
     *
     * @param  Application  $app  Application container
     * @param  JsonWebToken  $jwt  JWT to run through Validator
     * @param  JWK  $jwk  JWK to use for validation.
     */
    public function __construct(Application $app, JsonWebToken $jwt, JWK $jwk)
    {
        $this->app = $app;
        $this->jwt = $jwt;
        $this->jwk = $jwk;

        $this->errors = new MessageBag();
        $this->lastRunResult = null;
    }

    /**
     * Passes a Validator instance through a callback.
     *
     * @param  callable(Validator $validator): void  $callback
     * @return $this
     */
    public function passValidatorThru(callable $callback)
    {
        return $this->passThru($callback);
    }

    /**
     * Runs Validator rules through JWT
     *
     * @return $this
     */
    public function validate()
    {
        $this->passes();

        return $this;
    }

    /**
     * Checks if JWT passes Validator rules.
     *
     * @return bool True if JWT passes rules.
     */
    public function passes()
    {
        $validator = $this->buildValidator();

        $rules = $this->runThru($validator)->collectRules($validator);

        $this->errors = new MessageBag();

        $stopped = false;

        foreach ($rules as $rule) {
            try {
                $this->runRule($rule);
            } catch (RuleFailedException $ex) {
                $this->errors->add($this->getRuleIdentifier($rule), $ex->getMessage());

                if ($validator->getStopOnFailure()) {
                    $stopped = true;

                    break;
                }
            }
        }

        $this->lastRunResult = ! $stopped && $this->errors->isEmpty();

        foreach ($validator->getAfterValidation() as $after) {
            // Don't pass $this instance because of the risk of this method being called again (resulting in a stack overflow).
            $after($this->lastRunResult, $this->errors);
        }

        return $this->lastRunResult;
    }

    /**
     * Checks if JWT doesn't pass Validator rules.
     *
     * @return bool True if JWT doesn't pass rules.
     */
    public function fails()
    {
        return ! $this->passes();
    }

    /**
     * Gets errors from last validate.
     *
     * @return MessageBag
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Gets the result of the last run.
     *
     * @return bool|null Last run result or null if hasn't been run.
     */
    public function getLastRunResult()
    {
        return $this->lastRunResult;
    }

    /**
     * Builds a Validator
     */
    protected function buildValidator(): BuildsValidatorRules
    {
        $blacklistManager = $this->app->make(BlacklistManager::class);

        return new ExtendedValidator($this->app, $blacklistManager, $this->jwk);
    }

    /**
     * Collects rules from validator
     *
     * @return Rule[]
     */
    protected function collectRules(BuildsValidatorRules $validator): array
    {
        if ($validator instanceof ExtendedValidator) {
            return $this->collectRulesFromExtendedValidator($validator);
        }

        return $validator->getRulesBefore()->concat($validator->getRules())->all();
    }

    /**
     * Collects rules from ExtendedValidator instance
     *
     * @return Rule[]
     */
    protected function collectRulesFromExtendedValidator(ExtendedValidator $extendedValidator): array
    {
        $validator = Validator::createFrom($extendedValidator);

        foreach ($extendedValidator->getStack() as $callback) {
            $callback($validator);
        }

        return $validator->getRulesBefore()->concat($validator->getRules())->all();
    }

    /**
     * Gets unique identifier for Rule (to be used in error message bag)
     * If rule key is null, the fully qualified class name will be used.
     *
     * @return string
     */
    protected function getRuleIdentifier(Rule $rule)
    {
        return $rule->getKey() ?? get_class($rule);
    }

    /**
     * Runs a rule
     *
     * @return $this
     *
     * @throws RuleFailedException Thrown if rule failed.
     */
    protected function runRule(Rule $rule)
    {
        if (! $rule->passes($this->jwt)) {
            throw new RuleFailedException($rule, $rule->message());
        }

        return $this;
    }
}
