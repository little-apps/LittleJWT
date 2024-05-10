<?php

namespace LittleApps\LittleJWT\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;

trait HasUser
{
    /**
     * User (or null if not set)
     *
     * @var Authenticatable|null
     */
    protected $user;

    /**
     * Sets the user
     *
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the user
     *
     * @return Authenticatable|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
