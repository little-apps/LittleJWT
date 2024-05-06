<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use Illuminate\Contracts\Auth\Authenticatable;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Build\Options;
use LittleApps\LittleJWT\Concerns\HasUser;
use LittleApps\LittleJWT\Concerns\JWTHelpers;

class GuardBuildable
{
    use JWTHelpers;
    use HasUser;

    /**
     * Payload claims to include.
     *
     * @var array
     */
    protected $payloadClaims;

    /**
     * Header claims to include.
     *
     * @var array
     */
    protected $headerClaims;

    /**
     * Constructs a GuardBuildable instance.
     *
     * @param Authenticatable $user User to use for subject in JWT.
     * @param array $payloadClaims Any extra claims to include in the payload. (default: empty array)
     * @param array $headerClaims Any extra claims to include in the header. (default: empty array)
     */
    public function __construct(Authenticatable $user, array $payloadClaims = [], array $headerClaims = [])
    {
        $this->user = $user;
        $this->payloadClaims = $payloadClaims;
        $this->headerClaims = $headerClaims;
    }

    /**
     * Builds JWT for use with guard.
     *
     * @param Options $options
     * @return void
     */
    public function __invoke(Options $options)
    {
        $options
            ->sub($this->user->getAuthIdentifier())
            ->prv($this->hashSubjectModel($this->user));

        foreach ($this->payloadClaims as $key => $value) {
            $options->addPayloadClaim($key, $value);
        }

        foreach ($this->headerClaims as $key => $value) {
            $options->addHeaderClaim($key, $value);
        }
    }
}
