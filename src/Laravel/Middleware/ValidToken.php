<?php

namespace LittleApps\LittleJWT\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;

use LittleApps\LittleJWT\Concerns\RequestHasToken;
use LittleApps\LittleJWT\Exceptions\InvalidTokenException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\ValidatableBuilder;
use LittleApps\LittleJWT\Validation\Validatables\StackValidatable;

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
    public function handle(Request $request, Closure $next, ...$validatables)
    {
        $token = $this->getTokenForRequest($request);

        if (is_null($token) || ! $this->validate($token, $validatables)) {
            $this->invalid();
        }

        return $next($request);
    }

    /**
     * Runs the token through the validatables.
     *
     * @param string $token
     * @param iterable $validatables
     * @return bool
     */
    protected function validate($token, iterable $validatables)
    {
        if (empty($validatables)) {
            return LittleJWT::validateToken($token);
        }

        $stack = [];

        foreach ($validatables as $key) {
            $validatable = ValidatableBuilder::resolve($key);

            array_push($stack, $validatable);
        }

        $stackValidator = new StackValidatable($stack);

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
