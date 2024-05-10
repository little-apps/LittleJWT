<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Facades\Blacklist;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithTimeBackwardsCompatible;
use LittleApps\LittleJWT\Tests\TestCase;

class BlacklistTest extends TestCase
{
    use InteractsWithTimeBackwardsCompatible;
    use WithFaker;

    /**
     * Tests setting the default blacklist driver.
     *
     * @return void
     */
    public function test_set_default_blacklist_driver()
    {
        LittleJWT::fake();

        Blacklist::setDefaultDriver('cache');

        $jwt = LittleJWT::create();

        Blacklist::blacklist($jwt);

        $this->assertTrue(Blacklist::driver('cache')->isBlacklisted($jwt));
    }

    /**
     * Tests that the JTI for a JWT is blacklisted.
     *
     * @return void
     */
    public function test_jwt_blacklisted_jti()
    {
        LittleJWT::fake();

        $this->withBlacklistDrivers(['database', 'cache'], function ($driver) {
            $jwt = LittleJWT::create();

            $driver->blacklist($jwt);

            $this->assertTrue($driver->isBlacklisted($jwt));
        });
    }

    /**
     * Tests that the JTI for a JWT is blacklisted permanently.
     *
     * @return void
     */
    public function test_jwt_blacklisted_jti_permanently()
    {
        LittleJWT::fake();

        $this->withBlacklistDrivers(['database', 'cache'], function ($driver) {
            $jwt = LittleJWT::create();

            $driver->blacklist($jwt, 0);

            $this->assertTrue($driver->isBlacklisted($jwt));
        });
    }

    /**
     * Tests that a JWT is blacklisted using the hash of the entire JWT.
     *
     * @return void
     */
    public function test_jwt_blacklisted_jwt_hash()
    {
        LittleJWT::fake();

        $this->withBlacklistDrivers(['database', 'cache'], function ($driver) {
            $jwt = LittleJWT::create(function (Builder $builder) {
                $builder->remove('jti');
            });

            $driver->blacklist($jwt);

            $this->assertTrue($driver->isBlacklisted($jwt));
        });
    }

    /**
     * Tests that a JWT is blacklisted.
     *
     * @return void
     */
    public function test_jwt_not_blacklisted()
    {
        LittleJWT::fake();

        $this->withBlacklistDrivers(['database', 'cache'], function ($driver) {
            $jwt = LittleJWT::create();

            $this->assertFalse($driver->isBlacklisted($jwt));
        });
    }

    /**
     * Tests that blacklist is purged (by calling purge method).
     *
     * @return void
     */
    public function test_purged_pragmatically()
    {
        LittleJWT::fake();

        $this->withBlacklistDrivers(['database', 'cache'], function ($driver) {
            $jwt = LittleJWT::create();

            $driver->blacklist($jwt, 60);

            $this->assertTrue($driver->isBlacklisted($jwt));

            $this->travelTo(now()->addHours(24));

            $driver->purge();

            $this->assertFalse($driver->isBlacklisted($jwt));
        });
    }

    /**
     * Tests that blacklist is purged (using command).
     *
     * @return void
     */
    public function test_purged_command()
    {
        LittleJWT::fake();
        Blacklist::fake();

        $jwt = LittleJWT::create();

        Blacklist::blacklist($jwt, 60);

        $this->assertTrue(Blacklist::isBlacklisted($jwt));

        $original = Blacklist::getBlacklist();

        $this->travelTo(now()->addHours(24));

        $this
            ->artisan('littlejwt:purge')
            ->assertExitCode(0);

        $this->assertFalse(Blacklist::isBlacklisted($jwt));
        $this->assertNotEquals($original, Blacklist::getBlacklist());
    }

    /**
     * Tests that blacklist is not purged because an invalid driver is specified.
     *
     * @return void
     */
    public function test_not_purged_command()
    {
        LittleJWT::fake();
        Blacklist::fake();

        $jwt = LittleJWT::create();

        Blacklist::blacklist($jwt, 60);

        $this->assertTrue(Blacklist::isBlacklisted($jwt));

        $original = Blacklist::getBlacklist();

        $this->travelTo(now()->addHours(24));

        $this
            ->artisan('littlejwt:purge xyz')
            ->assertExitCode(1);

        $this->assertEquals($original, Blacklist::getBlacklist());
    }

    /**
     * Calls callback with BlacklistDriver instance for each specified driver
     *
     * @return void
     */
    protected function withBlacklistDrivers(array $drivers, callable $callback)
    {
        collect($drivers)
            ->map(fn ($driver) => Blacklist::driver($driver))
            ->each($callback);
    }
}
