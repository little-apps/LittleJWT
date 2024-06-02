<?php

namespace LittleApps\LittleJWT\Mutate;

use Closure;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\Validation\ValidatedJsonWebToken;

class MutatedValidatedJsonWebToken extends ValidatedJsonWebToken
{
    /**
     * Holds callback to unserialize JWT
     *
     * @var Closure(JsonWebToken): JsonWebToken
     */
    protected readonly Closure $unserializeCallback;

    /**
     * Unserialized/mutated JWT
     *
     * @var JsonWebToken|null
     */
    protected $unserialized;

    /**
     * Initializes instance
     *
     * @param  ValidatedJsonWebToken  $validated  Existing validated JWT
     * @param  callable(JsonWebToken): JsonWebToken  $unserializeCallback  Callback to unserialize JWT
     */
    public function __construct(ValidatedJsonWebToken $validated, callable $unserializeCallback)
    {
        parent::__construct($validated->getJWT(), $validated->passes());

        /*
         * The factory callback to unserialize is sent because
         * there maybe an error unserializing the JWT and we'll
         * wait for a call to unserialized() to throw that exception.
         */
        $this->unserializeCallback = Closure::fromCallable($unserializeCallback);
        $this->unserialized = null;
    }

    /**
     * Gets the unserialized JWT
     *
     * @return JsonWebToken
     */
    public function getUnserializedJWT()
    {
        return $this->unserialized();
    }

    /**
     * Gets the unserialized JWT
     *
     * @return JsonWebToken
     */
    public function unserialized()
    {
        if (is_null($this->unserialized)) {
            $this->unserialized = call_user_func($this->unserializeCallback);
        }

        return $this->unserialized;
    }
}
