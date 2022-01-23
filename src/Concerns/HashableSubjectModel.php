<?php

namespace LittleApps\LittleJWT\Concerns;

trait HashableSubjectModel {
    /**
     * Generates a hash for class.
     *
     * @param object|string $model If an object, resolves the class of object.
     * @return string
     */
    protected function hashSubjectModel($model) {
        $class = is_object($model) ? get_class($model) : $model;

        $hash = hash('sha256', $class);

        return $hash;
    }
}
