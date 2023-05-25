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

    /**
     * Sends back Request information
     *
     * @param Request $request
     * @return array
     */
    public function testIo(Request $request)
    {
        return [
            'method' => $request->method(),
            'url' => $request->url(),
            'fullUrl' => $request->fullUrl(),
            'headers' => $request->header(),
            'server' => $request->server(),
            'body' => $request->all(),
        ];
    }

    /**
     * Gets the JWT using the getJwt macro
     *
     * @param Request $request
     * @return array
     */
    public function testGetJwt(Request $request)
    {
        return $this->buildJsonResponseWithJwt($request->getJwt());
    }

    /**
     * Authenticates a user and sends back JWT
     *
     * @param Request $request
     * @return mixed
     */
    public function testLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::validate($credentials)) {
            return Auth::createJwtResponse(Auth::user());
        }

        return Response::json([
            'status' => 'error',
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    /**
     * Authenticates user and sends back JWT in JSON and header.
     *
     * @param Request $request
     * @return mixed
     */
    public function testLoginResponse(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::validate($credentials)) {
            $jwt = Auth::buildJwtForUser(Auth::user());

            return Response::withJwt($jwt)->attachJwt($jwt);
        }

        return Response::json([
            'status' => 'error',
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    /**
     * Authenticates user and responds using RespondsWithJWT trait
     *
     * @param Request $request
     * @return mixed
     */
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

    /**
     * Responds with logged in user information
     *
     * @param Request $request
     * @return mixed
     */
    public function testUser(Request $request)
    {
        return $request->user();
    }

    /**
     * Used to check if middleware check passed or failed.
     *
     * @param Request $request
     * @return array
     */
    public function testMiddleware(Request $request)
    {
        return ['status' => true];
    }
}
