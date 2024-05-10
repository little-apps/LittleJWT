<?php

namespace LittleApps\LittleJWT\Guards\Adapters\Concerns;

use Illuminate\Http\Request;

trait HasRequest
{
    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Gets the current request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the current request instance.
     *
     * @param  Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
