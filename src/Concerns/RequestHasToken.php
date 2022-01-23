<?php

namespace LittleApps\LittleJWT\Concerns;

use Illuminate\Http\Request;

trait RequestHasToken {
    /**
     * Get the token for the request.
     *
     * @param Request $request Request to get token from
     * @param string $inputKey Name of input to get token from (if it exists).
     *
     * @return string|null
     */
    public function getTokenForRequest(Request $request, $inputKey = 'token')
    {
        $token = $request->query($inputKey);

        if (empty($token)) {
            $token = $request->input($inputKey);
        }

        if (empty($token)) {
            $token = $request->bearerToken();
        }

        if (empty($token)) {
            $token = $request->getPassword();
        }

        return $token;
    }
}
