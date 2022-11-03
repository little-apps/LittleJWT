<?php

namespace LittleApps\LittleJWT\Concerns;

use Illuminate\Http\Request;

trait RequestHasToken
{
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
        $tokens = [
            $request->query($inputKey),
            $request->input($inputKey),
            $request->bearerToken(),
            $request->getPassword(),
        ];

        foreach ($tokens as $token) {
            if (!empty($token))
                return $token;
        }

        return null;
    }
}
