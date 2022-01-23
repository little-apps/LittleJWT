<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use LittleApps\LittleJWT\LittleJWT;
use LittleApps\LittleJWT\Verify\Verifiers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Illuminate\Support\Str;

class FingerprintAdapter extends AbstractAdapter {
    use Concerns\BuildsJwt, Concerns\HasRequest;

    protected $baseAdapter;

    public function __construct(Container $container, LittleJWT $jwt, GenericAdapter $adapter, array $config) {
        parent::__construct($container, $jwt, $config);

        $this->baseAdapter = $adapter;
    }

    /**
     * Creates a JWT with a fingerprint hash.
     *
     * @param Authenticatable $user
     * @param string $fingerprintHash
     * @return JWT
     */
    public function createJwtWithFingerprint(Authenticatable $user, string $fingerprintHash) {
        return $this->buildJwtForUser($user, [
            $this->getFingerprintClaimName() => $fingerprintHash
        ]);
    }

    /**
     * Creates a JWT response for an Authenticatable instance.
     *
     * @param Authenticatable|null $user The user to generate the JWT for.
     * @return \Illuminate\Http\JsonResponse Returns response with JWT
     */
    public function createJwtResponse(Authenticatable $user) {
        $fingerprint = $this->createFingerprint();

        $jwt = $this->createJwtWithFingerprint($user, $this->hashFingerprint($fingerprint));

        return
            ResponseFactory::withJwt($jwt)
                ->withCookie($this->getFingerprintCookieName(), $fingerprint);
    }

    /**
     * Gets the name for the fingerprint claim.
     *
     * @return string
     */
    public function getFingerprintClaimName() {
        return 'fgpt';
    }

    /**
     * Gets the name of the cookie that holds the fingeprint.
     *
     * @return string
     */
    public function getFingerprintCookieName() {
        return $this->config['cookie'] ?? 'fingerprint';
    }

    /**
     * Gets the value of the fingerprint cookie.
     *
     * @return string|null Fingerprint value or null if cookie doesn't exist.
     */
    public function getFingerprintCookieValue() {
        return $this->request->cookie($this->getFingerprintCookieName());
    }

    /**
     * Generates a value for the fingerprint cookie.
     *
     * @return string
     */
    public function createFingerprint() {
        return (string) Str::uuid();
    }

    /**
     * Hashes a fingerprint value for use in the JWT.
     *
     * @param string $fingerprint
     * @return string
     */
    public function hashFingerprint(string $fingerprint) {
        return hash('sha256', $fingerprint);
    }

    /**
     * Builds the verifier used to verify a JWT and retrieve a user.
     *
     * @return \LittleApps\LittleJWT\Contracts\Verifiable
     */
    protected function buildVerifier() {
        $fingerprintHash = $this->hashFingerprint($this->getFingerprintCookieValue() ?? '');

        return new Verifiers\StackVerifier([
            $this->baseAdapter->buildVerifier(),
            new Verifiers\FingerprintVerifier($fingerprintHash)
        ]);
    }
}
