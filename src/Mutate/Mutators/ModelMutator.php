<?php

namespace LittleApps\LittleJWT\Mutate\Mutators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LittleApps\LittleJWT\Contracts\Mutator;
use LittleApps\LittleJWT\JWT\JsonWebToken;

class ModelMutator implements Mutator
{
    /**
     * @inheritDoc
     */
    public function serialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        if (\is_subclass_of($value, Model::class)) {
            return $value->getKey();
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($value, string $key, array $args, JsonWebToken $jwt)
    {
        if (isset($args[0])) {
            [$table] = $args;

            if (\is_subclass_of($table, Model::class)) {
                try {
                    $model = new $table();

                    return $model->findOrFail($value);
                } catch (ModelNotFoundException $ex) {

                }

            }
        }

        return $value;
    }
}
