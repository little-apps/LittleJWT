<?php

namespace LittleApps\LittleJWT\Mutate\Mutatables;

use LittleApps\LittleJWT\Mutate\Mutators;

class DefaultMutatable
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = array_merge_recursive(['header' => [], 'payload' => []], $config);
    }

    public function __invoke(Mutators $mutators) {
        foreach ($this->config['header'] as $key => $value) {
            $mutators->addHeader($key, $value);
        }

        foreach ($this->config['payload'] as $key => $value) {
            $mutators->addPayload($key, $value);
        }
    }


}
