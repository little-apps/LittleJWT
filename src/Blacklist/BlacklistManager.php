<?php

namespace LittleApps\LittleJWT\Blacklist;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Manager;
use LittleApps\LittleJWT\Contracts\BlacklistDriver;

class BlacklistManager extends Manager
{
    /**
     * Gets the default driver to use.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('littlejwt.blacklist.driver');
    }

    /**
     * Create cache driver
     *
     * @return BlacklistDriver
     */
    public function createCacheDriver()
    {
        $manager = $this->container->make(CacheManager::class);

        return new Drivers\CacheDriver($manager, $this->config->get('littlejwt.blacklist.cache'));
    }

    /**
     * Create the database driver
     *
     * @return BlacklistDriver
     */
    public function createDatabaseDriver()
    {
        return new Drivers\DatabaseDriver($this->config->get('littlejwt.blacklist.database'));
    }

    /**
     * Set the default blacklist driver the factory should serve.
     *
     * @param  string  $name
     * @return $this
     */
    public function shouldUse($name)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->setDefaultDriver($name);

        return $this;
    }

    /**
     * Set the default blacklist driver name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setDefaultDriver($name)
    {
        $this->container['config']['littlejwt.blacklist.driver'] = $name;

        return $this;
    }
}
