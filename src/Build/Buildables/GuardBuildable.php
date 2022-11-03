<?php

namespace LittleApps\LittleJWT\Build\Buildables;

use Illuminate\Contracts\Auth\Authenticatable;

use LittleApps\LittleJWT\Build\Builder;

use LittleApps\LittleJWT\Concerns\HasUser;
use LittleApps\LittleJWT\Concerns\JWTHelpers;

use LittleApps\LittleJWT\Contracts\Buildable;

class GuardBuildable implements Buildable
{
    use JWTHelpers;
    use HasUser;

    protected $payloadClaims;

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

    public function build(Builder $builder)
    {
        $builder
            ->sub($this->user->getAuthIdentifier())
            ->prv($this->hashSubjectModel($this->user));

        foreach ($this->payloadClaims as $key => $value) {
            $builder->addPayloadClaim($key, $value);
        }

        foreach ($this->headerClaims as $key => $value) {
            $builder->addHeaderClaim($key, $value);
        }
    }
}
