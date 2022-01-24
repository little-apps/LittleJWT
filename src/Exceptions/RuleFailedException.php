<?php

namespace LittleApps\LittleJWT\Exceptions;

use Exception;
use LittleApps\LittleJWT\Contracts\Rule;

class RuleFailedException extends Exception
{
    /**
     * Rule that failed.
     *
     * @var \LittleApps\LittleJWT\Contracts\Rule
     */
    public $rule;

    public function __construct(Rule $rule, $message)
    {
        parent::__construct($message);

        $this->rule = $rule;
    }
}
