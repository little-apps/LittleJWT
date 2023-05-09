<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Mutate\Mutators;
use LittleApps\LittleJWT\Testing\Models\User;
use LittleApps\LittleJWT\Testing\TestBuildable;
use LittleApps\LittleJWT\Testing\TestMutator;
use LittleApps\LittleJWT\Testing\TestValidator;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\TestCase;
use Throwable;

class MutateTest extends TestCase
{
    use WithFaker;
    use CreatesUser;

    /**
     * Tests date claim is mutated
     *
     * @return void
     */
    public function test_date_claim_builder_mutated()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder
                ->tim($time)->as('date');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests date claim is mutated
     *
     * @return void
     */
    public function test_date_claim_mutators_mutated()
    {

        $time = time();

        $buildable = new TestBuildable(function (Builder $builder, Mutators $mutators) use ($time) {
            $builder->tim($time);
            $mutators->tim('date');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests date claim is mutated
     *
     * @return void
     */
    public function test_claim_builder_mutator_overrides_mutators()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder, Mutators $mutators) use ($time) {
            $builder->tim($time)->as('timestamp');
            $mutators->tim('date');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('U'), $jwt->getPayload()->get('tim'));
        $this->assertNotEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests date claim isnt mutated
     *
     * @return void
     */
    public function test_date_claim_not_mutated()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->tim($time);
        });

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
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->num(NAN)->as('float');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals('NaN', $jwt->getPayload()->get('num'));
    }

    /**
     * Tests mutator overrides default mutator in config file when built.
     *
     * @return void
     */
    public function test_buildable_mutator_override_config()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->iat($time)->as('date');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('iat'));
    }

    /**
     * Tests mutator override default mutator in config file after build.
     *
     * @return void
     */
    public function test_buildable_mutator_doesnt_override_config()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->iat($time);
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->iat('int');
        });

        $this->assertEquals(Carbon::parse($time), $jwt->getPayload()->get('iat'));
    }

    /**
     * Tests custom date/time is mutated.
     *
     * @return void
     */
    public function test_mutates_custom_datetime()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time)->as('custom_datetime:Y');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format('Y'), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests custom date/time is mutated and parsed.
     *
     * @return void
     */
    public function test_mutates_custom_datetime_parse()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time)->as('custom_datetime:Y');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('custom_datetime:Y');
        });

        $this->assertTrue(Carbon::parse($time)->eq($jwt->getPayload()->get('foo')));
    }

    /**
     * Tests date is mutated.
     *
     * @return void
     */
    public function test_mutates_date()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time)->as('date');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format(Mutators\DateMutator::$format), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests date is mutated and parsed.
     *
     * @return void
     */
    public function test_mutates_date_parse()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time)->as('date');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('date');
        });

        $this->assertTrue($jwt->getPayload()->get('foo')->isSameAs(Mutators\DateMutator::$format, Carbon::parse($time)));
    }

    /**
     * Tests date/time is mutated.
     *
     * @return void
     */
    public function test_mutates_datetime()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time)->as('datetime');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::parseToken($token);

        $this->assertEquals(Carbon::parse($time)->format(Mutators\DateTimeMutator::$format), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests date/time is mutated and parsed.
     *
     * @return void
     */
    public function test_mutates_datetime_parse()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->foo($time)->as('datetime');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('datetime');
        });

        $this->assertTrue($jwt->getPayload()->get('foo')->isSameAs(Mutators\DateTimeMutator::$format, Carbon::parse($time)));
    }

    /**
     * Tests decimal number is mutated.
     *
     * @return void
     */
    public function test_mutates_decimal()
    {
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('1234.1234')->as('decimal:2');
        });

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
        $mutator = new TestMutator(
            fn ($value) => strrev($value),
            fn ($value) => strrev($value),
        );

        $buildable = new TestBuildable(function (Builder $builder) use ($mutator) {
            $builder->foo('abcd')->as($mutator);
        });

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
        $mutator = new TestMutator(
            fn ($value) => strrev($value),
            fn ($value) => strrev($value),
        );

        $buildable = new TestBuildable(function (Builder $builder) use ($mutator) {
            $builder->foo('abcd')->as($mutator);
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) use ($mutator) {
            $mutators->foo($mutator);
        });

        $this->assertEquals('abcd', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests claim is mutated as array.
     *
     * @return void
     */
    public function test_mutates_array()
    {

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(['a', 'b', 'c', 'd'])->as('array');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('array');
        });

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
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(true)->as('bool');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('bool');
        });

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
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(1234.1234)->as('double');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('double');
        });

        $this->assertEquals(1234.1234, $jwt->getPayload()->get('foo'));
        $this->assertEquals('double', gettype($jwt->getPayload()->get('foo')));
    }

    /**
     * Tests claim is encrypted and decrypted.
     *
     * @return void
     */
    public function test_mutates_encrypted()
    {
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('secret')->as('encrypted');
        });

        $token = LittleJWT::createToken($buildable);

        $jwtEncrypted = LittleJWT::parseToken($token);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('encrypted');
        });

        $this->assertNotEquals('secret', $jwtEncrypted->getPayload()->get('foo'));
        $this->assertEquals('secret', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests an exception is thrown because a claim cannot be decrypted.
     *
     * @return void
     */
    public function test_cant_mutate_decrypted_throws()
    {
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder
                ->foo('secret');
        });

        $token = LittleJWT::createToken($buildable);

        try {
            LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
                $mutators->foo('encrypted');
            });

            $this->fail('Exception was not thrown.');
        } catch (Throwable $ex) {
            $this->assertInstanceOf(CantParseJWTException::class, $ex);
            $this->assertInstanceOf(DecryptException::class, $ex->inner);
        }


    }

    /**
     * Tests claim is mutated as integer.
     *
     * @return void
     */
    public function test_mutates_integer()
    {
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('1234')->as('int');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('int');
        });

        $this->assertEquals('integer', gettype($jwt->getPayload()->get('foo')));
        $this->assertEquals(1234, $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests claim is mutated to JSON.
     *
     * @return void
     */
    public function test_mutates_json()
    {
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(['a' => 'b', 'c' => 'd'])->as('json');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('json');
        });

        $this->assertEquals('array', gettype($jwt->getPayload()->get('foo')));
        $this->assertEquals(['a' => 'b', 'c' => 'd'], $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests claim mutates to object.
     *
     * @return void
     */
    public function test_mutates_object()
    {
        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo(['a' => 'b', 'c' => 'd'])->as('object');
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->foo('object');
        });

        $this->assertEquals('object', gettype($jwt->getPayload()->get('foo')));
        $this->assertEquals((object) ['a' => 'b', 'c' => 'd'], $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests a model claim is mutated.
     *
     * @return void
     */
    public function test_mutates_model()
    {
        $user = $this->user;

        $buildable = new TestBuildable(function (Builder $builder) use ($user) {
            $builder
                ->sub($user)->as(sprintf('model:%s', User::class));
        });

        $token = LittleJWT::createToken($buildable);

        $jwt = LittleJWT::mutateJWT(LittleJWT::parseToken($token), function (Mutators $mutators) {
            $mutators->sub(sprintf('model:%s', User::class));
        });

        $sub = $jwt->getPayload()->get('sub');

        $this->assertEquals(User::class, get_class($sub));
        $this->assertTrue($user->is($sub));
    }

    /**
     * Tests that a JWT is mutated.
     *
     * @return void
     */
    public function test_invoke_mutates_validatable_custom()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::createJWT(new TestBuildable(function (Builder $builder) {
            $builder
                ->foo('abcd');
        }));

        $validatable = new class () {
            public function __invoke(TestValidator $validator)
            {
                $validator
                    ->assertPasses()
                    ->assertClaimMatches('foo', 'dcba');
            }
        };

        $mutated = LittleJWT::mutateJWT($jwt, function (Mutators $mutators) {
            $mutators->foo(new TestMutator(
                fn ($value) => strrev($value),
                fn ($value) => strrev($value),
            ));
        });

        LittleJWT::validateToken((string) $mutated, $validatable, false);
    }

    /**
     * Tests that an invokable validatable class is mutated.
     *
     * @return void
     */
    public function test_invoke_mutates_custom_mapping()
    {
        LittleJWT::fake();

        $this->app->bind(TestMutator::class, function ($app) {
            return new TestMutator(
                fn ($value) => strrev($value),
                fn ($value) => strrev($value),
            );
        });

        LittleJWT::customMutator('test', TestMutator::class);

        $jwt = LittleJWT::createJWT(new TestBuildable(function (Builder $builder) {
            $builder
                ->foo('abcd')->as('test');
        }));

        $this->assertEquals('dcba', $jwt->getPayload()->get('foo'));
    }
}
