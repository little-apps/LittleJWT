<?php

namespace LittleApps\LittleJWT\Testing;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use LittleApps\LittleJWT\Concerns\RespondsWithJWT;

class TestController extends Controller
{
    use RespondsWithJWT;

    public function testResponseTrait(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::validate($credentials)) {
            $jwt = Auth::buildJwtForUser(Auth::user());

            if ($request->build === 'jwt') {
                $response = $this->buildJsonResponseWithJwt($jwt);
            } elseif ($request->build === 'token') {
                $response = $this->buildJsonResponseWithToken((string) $jwt, $jwt->getPayload()->get('exp'));
            } else {
                $response = response('');
            }

            if ($request->attach === 'header') {
                $this->attachJwtToResponseHeader($response, $jwt);
            }

            return $response;
        }

        return Response::json([
            'status' => 'error',
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }
}
