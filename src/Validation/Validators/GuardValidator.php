<?php

namespace LittleApps\LittleJWT\Validation\Validators;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;

use LittleApps\LittleJWT\Concerns\HashableSubjectModel;
use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

/**
 * The validator used by the guard.
 * This class is not responsible for fetching the associated user.
 */
class GuardValidator implements Validatable
{
    use HashableSubjectModel;

    protected $app;

    protected $config;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function validate(Validator $validator)
    {
        $contains = ['sub'];

        if ($this->config['exists']) {
            $validator->claimCallback('sub', function($value) {
                return !is_null(Auth::getProvider()->retrieveById($value));
            });
        }

        if (! empty($this->config['model'])) {
            array_push($contains, 'prv');

            $validator->secureEquals('prv', $this->hashSubjectModel($this->config['model']));
        }

        $validator->contains($contains);
    }
}
