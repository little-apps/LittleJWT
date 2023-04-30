<?php

namespace LittleApps\LittleJWT\JWT\Mutators;

use LittleApps\LittleJWT\Contracts\Mutator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ModelMutator implements Mutator
{
    /**
     * Serializes claim value
     *
     * @param mixed $value Unserialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return string|array|int
     */
    public function serialize($value, string $key, array $args, array $claims)
    {
        if (\is_subclass_of($value, Model::class)) {
            return $value->getKey();
        }

        return $value;
    }

    /**
     * Unserializes claim value
     *
     * @param string|array|int $value Serialized claim value
     * @param string $key Claim key
     * @param array $args Any arguments to use for mutation
     * @param array $claims All claims
     * @return mixed
     */
    public function unserialize($value, string $key, array $args, array $claims)
    {
        if (isset($args[0])) {
            [$table] = $args;

            if (\is_subclass_of($table, Model::class)) {
                try {
                    $model = new $table;

                    return $model->findOrFail($value);
                } catch (ModelNotFoundException $ex) {

                }

            }
        }

        return $value;
    }
}
