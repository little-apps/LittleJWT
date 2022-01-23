<?php

namespace LittleApps\LittleJWT\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;

trait HasUser {
    protected $user;

    /**
     * Sets the user
     *
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user) {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the user
     *
     * @return void
     */
    public function getUser() {
        return $this->user;
    }
}
