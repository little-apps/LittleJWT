<?php

namespace LittleApps\LittleJWT\Validation;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\MessageBag;

use Jose\Component\Core\JWK;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Contracts\Rule;
use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Exceptions\RuleFailedException;
use LittleApps\LittleJWT\JWT\JWT;

class Valid
{
    protected $app;

    protected $jwt;

    protected $jwk;

    protected $validatable;

    protected $errors;

    protected $lastRunResult;

    /**
     * Initializes a Valid instance
     *
     * @param Application $app Application container
     * @param JWT $jwt JWT to run through Validator
     * @param JWK $jwk JWK to use for validation.
     * @param Validatable $validatable Validatable to use to validate JWT
     */
    public function __construct(Application $app, JWT $jwt, JWK $jwk, Validatable $validatable)
    {
        $this->app = $app;
        $this->jwt = $jwt;
        $this->jwk = $jwk;
        $this->validatable = $validatable;

        $this->errors = new MessageBag();
        $this->lastRunResult = null;
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

        $rules = $validator->getRulesBefore()->concat($validator->getRules());

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
     * @return \Illuminate\Support\MessageBag
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
     *
     * @return Validator
     */
    protected function buildValidator()
    {
        $blacklistManager = $this->app->make(BlacklistManager::class);

        return tap(new Validator($blacklistManager, $this->jwk), function (Validator $validator) {
            $this->validatable->validate($validator);
        });
    }

    /**
     * Gets unique identifier for Rule (to be used in error message bag)
     *
     * @param Rule $rule
     * @return string
     */
    protected function getRuleIdentifier(Rule $rule)
    {
        return $rule->getKey() ?? get_class($rule);
    }

    /**
     * Runs a rule
     *
     * @param Rule $rule
     * @return $this
     * @throws \LittleApps\LittleJWT\Exceptions\RuleFailedException Thrown if rule failed.
     */
    protected function runRule(Rule $rule)
    {
        if (! $rule->passes($this->jwt)) {
            throw new RuleFailedException($rule, $rule->message());
        }

        return $this;
    }
}
