<?php

namespace LittleApps\LittleJWT\Core\Concerns;

trait AutoSigns {
    /**
     * Whether to auto-sign created JWTs.
     *
     * @var boolean
     */
    protected $autoSign = true;

    /**
     * Specifies if JWTs are auto-signed.
     *
     * @param boolean $enabled
     * @return $this
     */
    public function autoSign($enabled = true) {
        $this->autoSign = $enabled;

        return $this;
    }
}
