<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Testing\TestBuildable;
use LittleApps\LittleJWT\Tests\TestCase;

class MutateTest extends TestCase
{
    use WithFaker;

    /**
     * Tests date claim is mutated
     *
     * @return void
     */
    public function test_date_claim_mutated()
    {
        $mutators = [
            'payload' => [
                'tim' => 'date',
            ],
        ];

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->tim($time);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests date claim isnt mutated
     *
     * @return void
     */
    public function test_date_claim_not_mutated()
    {
        $mutators = [
        ];

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->tim($time);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertNotEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
        $this->assertEquals($time, $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests float claim is mutated
     *
     * @return void
     */
    public function test_float_claim_mutated()
    {
        $mutators = [
            'payload' => [
                'num' => 'float',
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->num(NAN);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals('NaN', $jwt->getPayload()->get('num'));
    }

    /**
     * Tests buildable mutator overrides default mutator in config file.
     *
     * @return void
     */
    public function test_buildable_mutator_override_config()
    {
        $mutators = [
            'payload' => [
                'iat' => 'date',
            ],
        ];

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->iat($time);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('iat'));
    }

    /**
     * Tests buildable mutator doesn't override default mutator in config file.
     *
     * @return void
     */
    public function test_buildable_mutator_doesnt_override_config()
    {
        $mutators = [
            'payload' => [
            ],
        ];

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->iat($time);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals($time, $jwt->getPayload()->get('iat'));
    }
}
