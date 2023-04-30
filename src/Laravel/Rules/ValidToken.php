<?php

namespace LittleApps\LittleJWT\Laravel\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

use LittleApps\LittleJWT\Facades\LittleJWT;

class ValidToken implements ImplicitRule
{
    /**
     * Validatable to use.
     *
     * @var callable(\LittleApps\LittleJWT\Validation\Validator): void
     */
    protected $callback;

    /**
     * Whether to apply default validatables.
     *
     * @var bool
     */
    protected $applyDefault;

    /**
     * Initializes implicit valid token rule.
     *
     * @param (callable(\LittleApps\LittleJWT\Validation\Validator): void)|null $callback Validatable to use.
     * @param bool $applyDefault Whether to apply default validatables (default: true)
     */
    public function __construct(callable $callback = null, $applyDefault = true)
    {
        $this->callback = $callback;
        $this->applyDefault = (bool) $applyDefault;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->validate($value);
    }

    /**
     * Performs the validation.
     *
     * @param mixed $token
     * @return bool
     */
    protected function validate($token)
    {
        return LittleJWT::validateToken(
            $token,
            $this->callback,
            $this->applyDefault
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is not a valid JSON Web Token (JWT).';
    }
}
