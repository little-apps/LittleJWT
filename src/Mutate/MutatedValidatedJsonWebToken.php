<?php

namespace LittleApps\LittleJWT\Mutate;

use Illuminate\Support\Traits\ForwardsCalls;
use LittleApps\LittleJWT\Validation\ValidatedJsonWebToken;

class MutatedValidatedJsonWebToken
{
    use ForwardsCalls;

    /**
     * Holds the validated JWT
     *
     * @var ValidatedJsonWebToken
     */
    protected $validated;

    /**
     * Mutate Handler
     *
     * @var MutateHandler
     */
    protected $handler;

    /**
     * Initializes instance
     *
     * @param ValidatedJsonWebToken $validated Existing validated JWT
     * @param MutateHandler $handler Mutate handler
     */
    public function __construct(ValidatedJsonWebToken $validated, MutateHandler $handler)
    {
        $this->validated = $validated;
        $this->handler = $handler;
    }

    /**
     * Gets the validated JWT
     *
     * @return ValidatedJsonWebToken
     */
    public function getValidatedJWT()
    {
        return $this->validated;
    }

    /**
     * Unserializes the existing JWT.
     *
     * @param Mutators|null $mutators
     * @return static
     */
    public function unserialize(Mutators $mutators = null)
    {
        // TODO: Test this method.
        $mutators = $mutators ?? new Mutators();

        return $this->handler->unserialize($this->getValidatedJWT()->getJWT(), $mutators);
    }

    /**
     * Forwards calls to validated JWT.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->forwardCallTo($this->getValidatedJWT(), $name, $arguments);
    }

    /**
     * Encodes validated JWT to string.
     *
     * @return string
     */
    public function __toString()
    {
        // This magic method needs to implemented, cause __call doesn't forward it.
        return (string) $this->getValidatedJWT();
    }
}
