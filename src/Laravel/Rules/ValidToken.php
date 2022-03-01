<?php

namespace LittleApps\LittleJWT\Laravel\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

use LittleApps\LittleJWT\Facades\LittleJWT;

class ValidToken implements ImplicitRule
{
    protected $callback;

    protected $applyDefault;

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
    protected function validate($token) {
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
