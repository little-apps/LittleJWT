<?php

namespace LittleApps\LittleJWT\Guards\Adapters;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Illuminate\Support\Str;

use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\Validation\Validatables;

class FingerprintAdapter extends AbstractAdapter
{
    use Concerns\BuildsJwt;
    use Concerns\HasRequest;

    /**
     * The hash algorithm to use for the fingerprint.
     *
     * @var string
     */
    const HASH_ALGORITHM = 'sha256';

    /**
     * Base adapter to add fingerprint for.
     *
     * @var AbstractAdapter
     */
    protected $baseAdapter;

    /**
     * Intializes fingerprint adapter.
     *
     * @param Container $container Application container.
     * @param GenericAdapter $adapter Adapter to add fingerprint for.
     * @param array $config Configuration options.
     */
    public function __construct(Container $container, GenericAdapter $adapter, array $config)
    {
        parent::__construct($container, $config);

        $this->baseAdapter = $adapter;
    }

    /**
     * Creates a JWT with a fingerprint hash.
     *
     * @param Authenticatable $user
     * @param string $fingerprintHash
     * @return JsonWebToken
     */
    public function createJwtWithFingerprint(Authenticatable $user, string $fingerprintHash)
    {
        return $this->buildJwtForUser($user, [
            $this->getFingerprintClaimName() => $fingerprintHash,
        ]);
    }

    /**
     * Creates a JWT response for an Authenticatable instance.
     *
     * @param Authenticatable $user The user to generate the JWT for.
     * @return \Illuminate\Http\JsonResponse Returns response with JWT
     */
    public function createJwtResponse(Authenticatable $user)
    {
        $fingerprint = $this->createFingerprint();

        $jwt = $this->createJwtWithFingerprint($user, $this->hashFingerprint($fingerprint));

        return
            ResponseFactory::withJwt($jwt)
                ->withCookie($this->getFingerprintCookieName(), $fingerprint, $this->getFingerprintCookieTtl());
    }

    /**
     * Gets the name for the fingerprint claim.
     *
     * @return string
     */
    public function getFingerprintClaimName()
    {
        return 'fgpt';
    }

    /**
     * Gets the name of the cookie that holds the fingeprint.
     *
     * @return string
     */
    public function getFingerprintCookieName()
    {
        return $this->config['cookie'] ?? 'fingerprint';
    }

    /**
     * Gets the cookies time to live.
     *
     * @return int Time to live (in minutes). 0 means forever.
     */
    public function getFingerprintCookieTtl()
    {
        return $this->config['ttl'] ?? 0;
    }

    /**
     * Gets the value of the fingerprint cookie.
     *
     * @return string|null Fingerprint value or null if cookie doesn't exist.
     */
    public function getFingerprintCookieValue()
    {
        return $this->request->cookie($this->getFingerprintCookieName());
    }

    /**
     * Generates a value for the fingerprint cookie.
     *
     * @return string
     */
    public function createFingerprint()
    {
        return (string) Str::uuid();
    }

    /**
     * Hashes a fingerprint value for use in the JWT.
     *
     * @param string $fingerprint
     * @return string
     */
    public function hashFingerprint(string $fingerprint)
    {
        return hash(static::HASH_ALGORITHM, $fingerprint);
    }

    /**
     * Gets a callback that receives  a Validator to specify the JWT validations.
     *
     * @return callable
     */
    protected function getValidatorCallback()
    {
        $fingerprintHash = $this->hashFingerprint($this->getFingerprintCookieValue() ?? '');

        $validatable = new Validatables\StackValidatable([
            $this->baseAdapter->getValidatorCallback(),
            new Validatables\FingerprintValidatable($fingerprintHash),
        ]);

        return $validatable;
    }
}
