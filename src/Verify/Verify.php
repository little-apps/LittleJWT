<?php

namespace LittleApps\LittleJWT\Verify;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\MessageBag;
use Jose\Component\Core\JWK;
use LittleApps\LittleJWT\Blacklist\BlacklistManager;
use LittleApps\LittleJWT\Contracts\Rule;

use LittleApps\LittleJWT\Contracts\Verifiable;
use LittleApps\LittleJWT\Exceptions\RuleFailedException;

use LittleApps\LittleJWT\JWT\JWT;

class Verify
{
    protected $app;

    protected $jwt;

    protected $jwk;

    protected $verifiable;

    protected $errors;

    protected $lastRunResult;

    /**
     * Initializes a Verify instance
     *
     * @param Application $app Application container
     * @param JWT $jwt JWT to run through Verifier
     * @param JWK $jwk JWK to use for verification.
     * @param Verifier $verifier JWK to use for verification.
     * @param Verifiable $verifiable Verifiable to use to build Verifier
     */
    public function __construct(Application $app, JWT $jwt, JWK $jwk, Verifiable $verifiable)
    {
        $this->app = $app;
        $this->jwt = $jwt;
        $this->jwk = $jwk;
        $this->verifiable = $verifiable;

        $this->errors = new MessageBag();
        $this->lastRunResult = null;
    }

    /**
     * Runs Verifier rules through JWT
     *
     * @return $this
     */
    public function verify()
    {
        $this->passes();

        return $this;
    }

    /**
     * Checks if JWT passes Verifier rules.
     *
     * @return bool True if JWT passes rules.
     */
    public function passes()
    {
        $verifier = $this->buildVerifier();

        $rules = $verifier->getRulesBefore()->concat($verifier->getRules());

        $this->errors = new MessageBag();

        $stopped = false;

        foreach ($rules as $rule) {
            try {
                $this->runRule($rule);
            } catch (RuleFailedException $ex) {
                $this->errors->add($this->getRuleIdentifier($rule), $ex->getMessage());

                if ($verifier->getStopOnFailure()) {
                    $stopped = true;

                    break;
                }
            }
        }

        $this->lastRunResult = ! $stopped && $this->errors->isEmpty();

        foreach ($verifier->getAfterVerify() as $after) {
            // Don't pass $this instance because of the risk of this method being called again (resulting in a stack overflow).
            $after($this->lastRunResult, $this->errors);
        }

        return $this->lastRunResult;
    }

    /**
     * Checks if JWT doesn't pass Verifier rules.
     *
     * @return bool True if JWT doesn't pass rules.
     */
    public function fails()
    {
        return ! $this->passes();
    }

    /**
     * Gets errors from last verify.
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
     * Builds a Verifier
     *
     * @return Verifier
     */
    protected function buildVerifier()
    {
        $blacklistManager = $this->app->make(BlacklistManager::class);

        return tap(new Verifier($blacklistManager, $this->jwk), function (Verifier $verifier) {
            $this->verifiable->verify($verifier);
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
