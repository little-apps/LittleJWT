<?php

namespace LittleApps\LittleJWT\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

use LittleApps\LittleJWT\Concerns\RequestHasToken;
use LittleApps\LittleJWT\Exceptions\InvalidTokenException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Validation\Validators\StackValidator;

class ValidToken
{
    use RequestHasToken;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$validators)
    {
        $token = $this->getTokenForRequest($request);

        if (is_null($token) || ! $this->validate($token, $validators)) {
            $this->invalid();
        }

        return $next($request);
    }

    /**
     * Runs the token through the validators.
     *
     * @param string $token
     * @param iterable $validators
     * @return bool
     */
    protected function validate($token, iterable $validators)
    {
        if (empty($validators)) {
            return LittleJWT::validateToken($token);
        }

        $stack = [];

        foreach ($validators as $validator) {
            $validatable = App::make("littlejwt.validators.{$validator}");

            array_push($stack, $validatable);
        }

        $stackValidator = new StackValidator($stack);

        return LittleJWT::validateToken($token, [$stackValidator, 'validate'], false);
    }

    /**
     * Called when token is invalid.
     *
     * @throws InvalidTokenException
     */
    protected function invalid()
    {
        throw new InvalidTokenException();
    }
}
