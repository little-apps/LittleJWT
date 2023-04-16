<?php

namespace LittleApps\LittleJWT\Validation\Validatables;

use Illuminate\Support\Facades\Auth;

use LittleApps\LittleJWT\Concerns\JWTHelpers;
use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

/**
 * The validator used by the generic guard adapter.
 * This class is not responsible for fetching the associated user.
 * @see https://docs.getlittlejwt.com/en/guard#generic-adapter-generic
 */
class GuardValidatable
{
    use JWTHelpers;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke(Validator $validator)
    {
        $contains = [];

        if ($this->config['exists']) {
            array_push($contains, 'sub');

            $validator->claimCallback('sub', function ($value) {
                return ! is_null(Auth::getProvider()->retrieveById($value));
            });
        }

        if (! empty($this->config['model'])) {
            array_push($contains, 'prv');

            $validator->secureEquals('prv', $this->hashSubjectModel($this->config['model']));
        }

        $validator->contains($contains);
    }
}
