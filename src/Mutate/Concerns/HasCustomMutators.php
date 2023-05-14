<?php

namespace LittleApps\LittleJWT\Mutate\Concerns;

trait HasCustomMutators
{
    /**
     * Custom mutator mappings
     *
     * @var array<string, \LittleApps\LittleJWT\Contracts\Mutator>
     */
    protected $customMutatorsMapping = [];

    /**
     * Sets custom mutator mapping
     *
     * @param string $key Key
     * @param class-string<\LittleApps\LittleJWT\Contracts\Mutator> $class Fully qualified class name
     * @return void
     */
    public function customMutator(string $key, string $class)
    {
        $this->customMutatorsMapping[$key] = $class;
    }

    /**
     * Gets custom mutator mappings
     *
     * @return array
     */
    public function getCustomMutators()
    {
        return $this->customMutatorsMapping;
    }
}
