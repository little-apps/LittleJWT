<?php

namespace LittleApps\LittleJWT\Validation\Validators;

use Illuminate\Contracts\Foundation\Application;

use LittleApps\LittleJWT\Concerns\HashableSubjectModel;
use LittleApps\LittleJWT\Contracts\Validatable;
use LittleApps\LittleJWT\Validation\Validator;

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

        if (! empty($this->config['model'])) {
            array_push($contains, 'prv');

            $validator->secureEquals('prv', $this->hashSubjectModel($this->config['model']));
        }

        $validator->contains($contains);
    }
}
