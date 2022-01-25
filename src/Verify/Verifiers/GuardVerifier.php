<?php

namespace LittleApps\LittleJWT\Verify\Verifiers;

use Illuminate\Contracts\Foundation\Application;
use LittleApps\LittleJWT\Concerns\HashableSubjectModel;
use LittleApps\LittleJWT\Contracts\Validatable;

use LittleApps\LittleJWT\Verify\Validator;

class GuardVerifier implements Validatable
{
    use HashableSubjectModel;

    protected $app;

    protected $model;

    public function __construct(Application $app, string $model)
    {
        $this->app = $app;
        $this->model = $model;
    }

    public function verify(Validator $verifier)
    {
        $contains = ['sub'];

        if (! is_null($this->model)) {
            array_push($contains, 'prv');

            $verifier->secureEquals('prv', $this->hashSubjectModel($this->model));
        }

        $verifier->contains($contains);
    }
}
