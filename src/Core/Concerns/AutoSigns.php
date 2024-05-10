<?php

namespace LittleApps\LittleJWT\Core\Concerns;

trait AutoSigns
{
    /**
     * Whether to auto-sign created JWTs.
     *
     * @var bool
     */
    protected $autoSign = true;

    /**
     * Specifies if JWTs are auto-signed.
     *
     * @param  bool  $enabled
     * @return $this
     */
    public function autoSign($enabled = true)
    {
        $this->autoSign = $enabled;

        return $this;
    }
}
