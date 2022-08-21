<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;

use LittleApps\LittleJWT\Facades\Blacklist;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithTimeBackwardsCompatible;

class BlacklistTest extends TestCase
{
    use InteractsWithTimeBackwardsCompatible;
    use WithFaker;

    /**
     * Tests that a JWT is blacklisted.
     *
     * @return void
     */
    public function test_jwt_blacklisted()
    {
        LittleJWT::fake();
        Blacklist::fake();

        $jwt = LittleJWT::createJWT();

        Blacklist::blacklist($jwt);

        $this->assertTrue(Blacklist::isBlacklisted($jwt));
    }

    /**
     * Tests that a JWT is blacklisted.
     *
     * @return void
     */
    public function test_jwt_not_blacklisted()
    {
        LittleJWT::fake();
        Blacklist::fake();

        $jwt = LittleJWT::createJWT();

        $this->assertFalse(Blacklist::isBlacklisted($jwt));
    }

    /**
     * Tests that blacklist is purged (by calling purge method).
     *
     * @return void
     */
    public function test_purged_pragmatically()
    {
        LittleJWT::fake();
        Blacklist::fake();

        $jwt = LittleJWT::createJWT();

        Blacklist::blacklist($jwt, 60);

        $this->assertTrue(Blacklist::isBlacklisted($jwt));

        $this->travelTo(now()->addHours(24));

        Blacklist::purge();

        $this->assertFalse(Blacklist::isBlacklisted($jwt));
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

        $jwt = LittleJWT::createJWT();

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

        $jwt = LittleJWT::createJWT();

        Blacklist::blacklist($jwt, 60);

        $this->assertTrue(Blacklist::isBlacklisted($jwt));

        $original = Blacklist::getBlacklist();

        $this->travelTo(now()->addHours(24));

        $this
            ->artisan('littlejwt:purge xyz')
                ->assertExitCode(1);

        $this->assertEquals($original, Blacklist::getBlacklist());
    }
}
