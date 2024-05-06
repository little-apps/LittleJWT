<?php

namespace LittleApps\LittleJWT\Mutate;

use Illuminate\Support\Traits\ForwardsCalls;
use LittleApps\LittleJWT\Factories\JWTBuilder;
use LittleApps\LittleJWT\JWT\ClaimManager;
use LittleApps\LittleJWT\JWT\JsonWebToken;
use LittleApps\LittleJWT\JWT\MutatedJsonWebToken;

class Mutate
{
    use ForwardsCalls;

    /**
     * Builder for JWTs.
     *
     * @var JWTBuilder
     */
    protected $builder;

    /**
     * Mutator Manager
     *
     * @var MutatorManager
     */
    protected $mutatorManager;

    /**
     * Initializes Mutate instance.
     *
     * @param JWTBuilder $builder
     * @param MutatorManager $mutatorManager
     */
    public function __construct(JWTBuilder $builder, MutatorManager $mutatorManager)
    {
        $this->builder = $builder;
        $this->mutatorManager = $mutatorManager;
    }

    /**
     * Serializes claims in a JWT
     *
     * @param Mutators $mutators
     * @param JsonWebToken $jwt
     * @return JsonWebToken Unsigned JWT
     */
    public function serialize(Mutators $mutators, JsonWebToken $jwt)
    {
        $headers = $this->serializeHeaders($mutators, $jwt);
        $payload = $this->serializePayload($mutators, $jwt);

        return $this->builder->buildFromParts($headers, $payload);
    }

    /**
     * Unserializes claims in a JWT
     *
     * @param Mutators $mutators
     * @param JsonWebToken $jwt
     * @return MutatedJsonWebToken Unserialized JWT
     */
    public function unserialize(Mutators $mutators, JsonWebToken $jwt)
    {
        $headers = new ClaimManager(ClaimManager::PART_HEADER, $this->unserializeHeaders($mutators, $jwt));
        $payload = new ClaimManager(ClaimManager::PART_PAYLOAD, $this->unserializePayload($mutators, $jwt));

        return new MutatedJsonWebToken($jwt, $headers, $payload);
    }

    /**
     * Serializes header claims.
     *
     * @param Mutators $mutators
     * @param JsonWebToken $jwt
     * @return array
     */
    protected function serializeHeaders(Mutators $mutators, JsonWebToken $jwt)
    {
        $headers = [];

        foreach ($jwt->getHeaders()->mapToValues()->toArray() as $key => $value) {
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

    /**
     * Serializes payload claims.
     *
     * @param Mutators $mutators
     * @param JsonWebToken $jwt
     * @return array
     */
    protected function serializePayload(Mutators $mutators, JsonWebToken $jwt)
    {
        $payload = [];

        foreach ($jwt->getPayload()->mapToValues()->toArray() as $key => $value) {
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

    /**
     * Unserializes header claims.
     *
     * @param Mutators $mutators
     * @param JsonWebToken $jwt
     * @return array
     */
    protected function unserializeHeaders(Mutators $mutators, JsonWebToken $jwt)
    {
        $headers = [];

        foreach ($jwt->getHeaders()->mapToValues()->toArray() as $key => $value) {
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

    /**
     * Unserializes payload claims.
     *
     * @param Mutators $mutators
     * @param JsonWebToken $jwt
     * @return array
     */
    protected function unserializePayload(Mutators $mutators, JsonWebToken $jwt)
    {
        $payload = [];

        foreach ($jwt->getPayload()->mapToValues()->toArray() as $key => $value) {
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
