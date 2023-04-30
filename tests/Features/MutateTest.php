<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\JWT\Mutators;
use LittleApps\LittleJWT\Testing\Models\User;
use LittleApps\LittleJWT\Testing\TestBuildable;
use LittleApps\LittleJWT\Testing\TestMutator;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\TestCase;

class MutateTest extends TestCase
{
    use WithFaker;
    use CreatesUser;

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

        $jwt = LittleJWT::parseToken($token, ['payload' => ['iat' => 'none']]);

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

        $this->assertEquals(Carbon::parse($time), $jwt->getPayload()->get('iat'));
    }

    /**
     * Tests custom date/time is mutated.
     *
     * @return void
     */
    public function test_mutates_custom_datetime()
    {
        $mutators = [
            'payload' => [
                'foo' => 'custom_datetime:Y',
            ],
        ];

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('Y'), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests date is mutated.
     *
     * @return void
     */
    public function test_mutates_date()
    {
        $mutators = [
            'payload' => [
                'foo' => 'date',
            ],
        ];

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format(Mutators\DateMutator::$format), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests date/time is mutated.
     *
     * @return void
     */
    public function test_mutates_datetime()
    {
        $mutators = [
            'payload' => [
                'foo' => 'datetime',
            ],
        ];

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format(Mutators\DateTimeMutator::$format), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests decimal number is mutated.
     *
     * @return void
     */
    public function test_mutates_decimal()
    {
        $mutators = [
            'payload' => [
                'foo' => 'decimal:2',
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('1234.1234');
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(1234.12, $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests claim is serialized, but not unserialized.
     *
     * @return void
     */
    public function test_mutates_custom()
    {
        $mutators = [
            'payload' => [
                'foo' => new TestMutator(
                    fn ($value) => strrev($value),
                    fn ($value) => strrev($value),
                ),
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('abcd');
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals('dcba', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests claim is serialized and unserialized.
     *
     * @return void
     */
    public function test_mutates_custom_reverse()
    {
        $mutators = [
            'payload' => [
                'foo' => new TestMutator(
                    fn ($value) => strrev($value),
                    fn ($value) => strrev($value),
                ),
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('abcd');
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token, $mutators);

        $this->assertEquals('abcd', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests claim is mutated as array.
     *
     * @return void
     */
    public function test_mutates_array()
    {
        $mutators = [
            'payload' => [
                'foo' => 'array',
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(['a', 'b', 'c', 'd']);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token, $mutators);

        $this->assertEquals('array', gettype($jwt->getPayload()->get('foo')));
        $this->assertEquals(['a', 'b', 'c', 'd'], $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests claim is mutated as boolean.
     *
     * @return void
     */
    public function test_mutates_bool()
    {
        $mutators = [
            'payload' => [
                'foo' => 'bool',
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(true);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token, $mutators);

        $this->assertEquals(true, $jwt->getPayload()->get('foo'));
        $this->assertEquals('boolean', gettype($jwt->getPayload()->get('foo')));
    }

    /**
     * Tests claim is mutated as double.
     *
     * @return void
     */
    public function test_mutates_double()
    {
        $mutators = [
            'payload' => [
                'foo' => 'double',
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(1234.1234);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token, $mutators);

        $this->assertEquals(1234.1234, $jwt->getPayload()->get('foo'));
        $this->assertEquals('double', gettype($jwt->getPayload()->get('foo')));
    }

    /**
     * Tests claim is encrypted and decrypt
     *
     * @return void
     */
    public function test_mutates_encrypted()
    {
        $mutators = [
            'payload' => [
                'foo' => 'encrypted',
            ],
        ];

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('secret');
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwtEncrypted = LittleJWT::parseToken($token);
        $jwt = LittleJWT::parseToken($token, $mutators);

        $this->assertNotEquals('secret', $jwtEncrypted->getPayload()->get('foo'));
        $this->assertEquals('secret', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests a model claim is mutated.
     *
     * @return void
     */
    public function test_mutates_model()
    {
        $user = $this->user;

        $mutators = [
            'payload' => [
                'sub' => sprintf('model:%s', User::class),
            ],
        ];


        $buildable = new TestBuildable(function (Builder $builder) use ($user) {
            $builder
                ->sub($user);
        }, $mutators);

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token, $mutators);

        $this->assertEquals(User::class, get_class($jwt->getPayload()->get('sub')));
        $this->assertTrue($user->is($jwt->getPayload()->get('sub')));
    }
}
