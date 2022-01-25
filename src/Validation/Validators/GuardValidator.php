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

    protected $model;

    public function __construct(Application $app, string $model)
    {
        $this->app = $app;
        $this->model = $model;
    }

    public function validate(Validator $validator)
    {
        $contains = ['sub'];

        if (! is_null($this->model)) {
            array_push($contains, 'prv');

            $validator->secureEquals('prv', $this->hashSubjectModel($this->model));
        }

        $validator->contains($contains);
    }
}
