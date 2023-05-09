<?php

namespace LittleApps\LittleJWT\Mutate;

use Illuminate\Support\Traits\ForwardsCalls;

use LittleApps\LittleJWT\Concerns\PassableThru;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\MutatedJsonWebToken;

class Mutate
{
    use ForwardsCalls;
    use PassableThru;

    /**
     * Mutator Manager
     *
     * @var MutatorManager
     */
    protected $mutatorManager;

    public function __construct(MutatorManager $mutatorManager)
    {
        $this->mutatorManager = $mutatorManager;
    }

    /**
     * Passes a Mutators instance through a callback.
     *
     * @param callable(Mutators $mutators) $callback
     * @return $this
     */
    public function passMutatorsThru(callable $callback)
    {
        return $this->passThru($callback);
    }

    /**
     * Serializes claims in a JWT
     *
     * @param JsonWebToken $jwt
     * @return JsonWebToken Unsigned JWT
     */
    public function serialize(JsonWebToken $jwt)
    {
        $this->runThru($mutators = new Mutators());

        $headers = $this->serializeHeaders($mutators, $jwt);
        $payload = $this->serializePayload($mutators, $jwt);

        $builder = new JWTBuilder();

        return $builder->buildFromParts($headers, $payload);
    }

    /**
     * Unserializes claims in a JWT
     *
     * @param JsonWebToken $jwt
     * @return MutatedJsonWebToken Unserialized JWT
     */
    public function unserialize(JsonWebToken $jwt)
    {
        $this->runThru($mutators = new Mutators());

        $headers = $this->unserializeHeaders($mutators, $jwt);
        $payload = $this->unserializePayload($mutators, $jwt);

        return new MutatedJsonWebToken($jwt, $headers, $payload);
    }

    protected function serializeHeaders(Mutators $mutators, JsonWebToken $jwt)
    {
        $headers = [];

        foreach ($jwt->getHeaders()->toArray() as $key => $value) {
            if ($mutators->hasHeader($key)) {
                $definition = $mutators->getHeaders($key);

                $headers[$key] = $this->mutatorManager->serialize($key, $definition, $value, $jwt);
            } elseif ($mutators->hasGlobal($key)) {
                $definition = $mutators->getGlobal($key);

                $headers[$key] = $this->mutatorManager->serialize($key, $definition, $value, $jwt);
            } else {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    protected function serializePayload(Mutators $mutators, JsonWebToken $jwt)
    {
        $payload = [];

        foreach ($jwt->getPayload()->toArray() as $key => $value) {
            if ($mutators->hasPayload($key)) {
                $definition = $mutators->getPayload($key);

                $payload[$key] = $this->mutatorManager->serialize($key, $definition, $value, $jwt);
            } elseif ($mutators->hasGlobal($key)) {
                $definition = $mutators->getGlobal($key);

                $payload[$key] = $this->mutatorManager->serialize($key, $definition, $value, $jwt);
            } else {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }

    protected function unserializeHeaders(Mutators $mutators, JsonWebToken $jwt)
    {
        $headers = [];

        foreach ($jwt->getHeaders()->toArray() as $key => $value) {
            if ($mutators->hasHeader($key)) {
                $definition = $mutators->getHeaders($key);

                $headers[$key] = $this->mutatorManager->unserialize($key, $definition, $value, $jwt);
            } elseif ($mutators->hasGlobal($key)) {
                $definition = $mutators->getGlobal($key);

                $headers[$key] = $this->mutatorManager->unserialize($key, $definition, $value, $jwt);
            }
        }

        return $headers;
    }

    protected function unserializePayload(Mutators $mutators, JsonWebToken $jwt)
    {
        $payload = [];

        foreach ($jwt->getPayload()->toArray() as $key => $value) {
            if ($mutators->hasPayload($key)) {
                $definition = $mutators->getPayload($key);

                $payload[$key] = $this->mutatorManager->unserialize($key, $definition, $value, $jwt);
            } elseif ($mutators->hasGlobal($key)) {
                $definition = $mutators->getGlobal($key);

                $payload[$key] = $this->mutatorManager->unserialize($key, $definition, $value, $jwt);
            }
        }

        return $payload;
    }
}
