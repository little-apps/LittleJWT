<?php

namespace LittleApps\LittleJWT\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use LittleApps\LittleJWT\Exceptions\InvalidTokenException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Factories\ValidatableBuilder;
use LittleApps\LittleJWT\Validation\Validatables\StackValidatable;

class ValidToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$validatables)
    {
        $token = $request->getToken();

        if (is_null($token) || ! $this->validate($token, $validatables)) {
            $this->invalid();
        }

        return $next($request);
    }

    /**
     * Runs the token through the validatables.
     *
     * @param  string  $token
     * @return bool
     */
    protected function validate($token, iterable $validatables)
    {
        $jwt = LittleJWT::parse($token);

        if (empty($validatables)) {
            return LittleJWT::validate($jwt)->passes();
        }

        $stack = array_map(fn ($key) => ValidatableBuilder::resolve($key), (array) $validatables);

        $stackValidator = new StackValidatable($stack);

        return LittleJWT::validate($jwt, $stackValidator, false)->passes();
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
