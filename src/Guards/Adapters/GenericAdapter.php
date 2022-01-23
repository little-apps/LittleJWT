<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use LittleApps\LittleJWT\Verify\Verifiers;

class GenericAdapter extends AbstractAdapter {
    use Concerns\BuildsJwt;

    /**
     * Builds the verifier used to verify a JWT and retrieve a user.
     *
     * @return \LittleApps\LittleJWT\Contracts\Verifiable
     */
    protected function buildVerifier() {
        return new Verifiers\GuardVerifier($this->container, $this->config['model']);
    }
}
