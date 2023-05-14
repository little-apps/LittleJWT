<?php

namespace LittleApps\LittleJWT\Mutate\Concerns;

trait HasDefaultMutators
{
    /**
     * Whether to apply default mutators.
     *
     * @var boolean
     */
    protected $defaultMutators;

    /**
     * Sets whether to apply default mutators or not
     *
     * @param boolean $enabled
     * @return $this
     */
    public function applyDefaultMutators(bool $enabled = true) {
        $this->defaultMutators = $enabled;

        return $this;
    }
}
