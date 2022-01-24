<?php

namespace LittleApps\LittleJWT\Middleware;

use Closure;
use Illuminate\Http\Request;

use LittleApps\LittleJWT\Concerns\RespondsWithJWT;
use LittleApps\LittleJWT\Facades\LittleJWT;

class AttachToResponse
{
    use RespondsWithJWT;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $jwt = LittleJWT::getJwt();

        return ! is_null($jwt) ? $this->attachJwtToResponseHeader($response, $jwt) : $response;
    }
}
