<?php

namespace LittleApps\LittleJWT\Concerns;

trait ExtractsMutators
{
    /**
     * Checks if callback is instance and has getMutators method.
     *
     * @param mixed $callback
     * @return bool
     */
    protected function hasMutators($callback)
    {
        return method_exists($callback, 'getMutators');
    }

    /**
     * Extracts mutators from invokable callback.
     *
     * @param mixed $callback
     * @return array{'header': list<\LittleApps\LittleJWT\Contracts\Mutator>, 'payload': list<\LittleApps\LittleJWT\Contracts\Mutator>}
     */
    protected function extractMutators($callback)
    {
        $extracted = ['header' => [], 'payload' => []];

        $mutators = $callback->getMutators();

        if (isset($mutators['header']) && is_array($mutators['header'])) {
            $extracted['header'] = array_merge($extracted['header'], $mutators['header']);
        }

        if (isset($mutators['payload']) && is_array($mutators['payload'])) {
            $extracted['payload'] = array_merge($extracted['payload'], $mutators['payload']);
        }

        return $extracted;
    }
}
