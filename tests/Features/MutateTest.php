<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Exceptions\CantParseJWTException;
use LittleApps\LittleJWT\Exceptions\CantResolveMutator;
use LittleApps\LittleJWT\Exceptions\ModelNotFoundException;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Mutate\Mutatables\StackMutatable;
use LittleApps\LittleJWT\Mutate\MutatorResolver;
use LittleApps\LittleJWT\Mutate\Mutators;
use LittleApps\LittleJWT\Mutate\Mutators\DoubleMutator;
use LittleApps\LittleJWT\Mutate\Mutators\EncryptMutator;
use LittleApps\LittleJWT\Mutate\Mutators\StackMutator;
use LittleApps\LittleJWT\Testing\Models\User;
use LittleApps\LittleJWT\Testing\TestBuildable;
use LittleApps\LittleJWT\Testing\TestMutator;
use LittleApps\LittleJWT\Testing\TestValidator;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\TestCase;
use Throwable;

class MutateTest extends TestCase
{
    use CreatesUser;
    use WithFaker;

    /**
     * Tests JWT is created, mutated, and signed.
     *
     * @return void
     */
    public function test_basic_mutate_create_sign()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder
                ->tim($time);
        });

        $signed =
            LittleJWT::withMutate()
                ->mutate(function (Mutators $mutators) {
                    $mutators
                        ->tim('date');
                })
                ->create($buildable);

        $jwt = LittleJWT::parse((string) $signed);

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests that an invokable validatable class is mutated.
     *
     * @return void
     */
    public function test_basic_custom_mutate_create_sign()
    {
        LittleJWT::fake();

        $this->app->bind(TestMutator::class, function ($app) {
            return new TestMutator(
                fn ($value) => strrev($value),
                fn ($value) => strrev($value),
            );
        });

        LittleJWT::customMutator('test', TestMutator::class);

        $signed =
            LittleJWT::withMutate()
                ->mutate(function (Mutators $mutators) {
                    $mutators
                        ->foo('test');
                })
                ->create(function (Builder $builder) {
                    $builder
                        ->foo('abcd');
                });

        $jwt = LittleJWT::parse($signed);

        $this->assertEquals('dcba', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests JWT is validated and mutated.
     *
     * @return void
     */
    public function test_basic_validate_mutate()
    {
        LittleJWT::fake();

        $time = time();

        $signed =
            LittleJWT::withMutate()
                ->mutate(function (Mutators $mutators) {
                    $mutators
                        ->tim('date');
                })
                ->create(function (Builder $builder) use ($time) {
                    $builder
                        ->tim($time);
                });

        $mutated =
            LittleJWT::handler()
                ->mutate(function (Mutators $mutators) {
                    $mutators
                        ->tim('date');
                })
                ->validate($signed, function (TestValidator $validator) {
                    $validator
                        ->assertPasses()
                        ->assertClaimsExists('tim');
                });

        $jwt = LittleJWT::parse((string) $mutated->getJWT());

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests JWT is not mutated.
     *
     * @return void
     */
    public function test_cant_basic_validate_mutate()
    {
        LittleJWT::fake();

        $time = time();

        $signed =
            LittleJWT::handler()
                ->create(function (Builder $builder) use ($time) {
                    $builder
                        ->tim($time);
                });

        $mutated =
            LittleJWT::handler()
                ->mutate(function (Mutators $mutators) {
                    $mutators
                        ->tim('date');
                })
                ->validate($signed, function (TestValidator $validator) {
                    $validator
                        ->assertPasses();
                });

        $jwt = LittleJWT::parse((string) $mutated);

        $this->assertEquals($time, $jwt->getPayload()->get('tim'));
        $this->assertNotEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
    }

    /**
     * Tests date claim is mutated
     *
     * @return void
     */
    public function test_date_claim_mutators_mutated()
    {
        $time = time();

        $buildable = new TestBuildable(function (Builder $builder) use ($time) {
            $builder->tim($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->tim('date');
            })
            ->create($buildable);

        $jwt = LittleJWT::parse($token);

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('tim'));
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

        $token = (string) LittleJWT::create($buildable);

        $jwt = LittleJWT::parse($token);

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
            $builder->num(NAN);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->num('float');
            })->create($buildable);

        $jwt = LittleJWT::parse($token);

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
            $builder->iat($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->iat('date', 'payload');
            })->create($buildable);

        $jwt = LittleJWT::parse($token);

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

        $token = (string) LittleJWT::create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->iat('int');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('custom_datetime:Y');
            })->create($buildable);

        $jwt = LittleJWT::parse($token);

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
            $builder->foo($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('custom_datetime:Y');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('custom_datetime:Y');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('date');
            })->create($buildable);

        $jwt = LittleJWT::parse($token);

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
            $builder->foo($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('date');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('date');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('datetime');
            })->create($buildable);

        $jwt = LittleJWT::parse($token);

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
            $builder->foo($time);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('datetime');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('datetime');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo('1234.1234');
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('decimal:2');
            })->create($buildable);

        $jwt = LittleJWT::parse($token);

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

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('abcd');
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($mutator) {
                $mutators->foo($mutator);
            })->create($buildable);

        $jwt = LittleJWT::parse($token);

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

        $buildable = new TestBuildable(function (Builder $builder) {
            $builder->foo('abcd');
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($mutator) {
                $mutators->foo($mutator);
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($mutator) {
                $mutators->foo($mutator);
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo(['a', 'b', 'c', 'd']);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('array');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('array');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo(true);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('bool');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('bool');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo(1234.1234);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('double');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('double');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo('secret');
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('encrypted');
            })->create($buildable);

        $jwtEncrypted = LittleJWT::parse($token);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('encrypted');
            })
            ->unserialize(LittleJWT::parse($token));

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

        $token = (string) LittleJWT::create($buildable);

        try {
            LittleJWT::handler()
                ->mutate(function (Mutators $mutators) {
                    $mutators->foo('encrypted');
                })
                ->unserialize(LittleJWT::parse($token));

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
            $builder->foo('1234');
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('int');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('int');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo(['a' => 'b', 'c' => 'd']);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('json');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('json');
            })
            ->unserialize(LittleJWT::parse($token));

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
            $builder->foo(['a' => 'b', 'c' => 'd']);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('object');
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('object');
            })
            ->unserialize(LittleJWT::parse($token));

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
                ->sub($user);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->sub(sprintf('model:%s', User::class));
            })->create($buildable);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->sub(sprintf('model:%s', User::class));
            })
            ->unserialize(LittleJWT::parse($token));

        $sub = $jwt->getPayload()->get('sub');

        $this->assertEquals(User::class, get_class($sub));
        $this->assertTrue($user->is($sub));
    }

    /**
     * Tests a model claim is mutated that no longer exists.
     *
     * @return void
     */
    public function test_mutates_model_doesnt_exist()
    {
        $user = $this->user;

        $buildable = new TestBuildable(function (Builder $builder) use ($user) {
            $builder
                ->sub($user);
        });

        $token = (string) LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->sub(sprintf('model:%s', User::class));
            })->create($buildable);

        $user->delete();

        try {
            LittleJWT::handler()
                ->mutate(function (Mutators $mutators) {
                    $mutators->sub(sprintf('model:%s', User::class));
                })
                ->unserialize(LittleJWT::parse($token));

            $this->fail("The exception '".CantParseJWTException::class."' was not thrown.");
        } catch (CantParseJWTException $ex) {
            $this->assertNotNull($ex->inner);
            $this->assertEquals(ModelNotFoundException::class, get_class($ex->inner));
        }
    }

    /**
     * Tests that a JWT is mutated.
     *
     * @return void
     */
    public function test_invoke_mutates_validatable_custom()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create(new TestBuildable(function (Builder $builder) {
            $builder
                ->foo('abcd');
        }));

        $validatable = new class
        {
            public function __invoke(TestValidator $validator)
            {
                $validator
                    ->withoutDefaults()
                    ->assertPasses()
                    ->assertClaimMatches('foo', 'dcba');
            }
        };

        $mutated = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo(new TestMutator(
                    fn ($value) => strrev($value),
                    fn ($value) => strrev($value),
                ));
            })
            ->unserialize(LittleJWT::parse((string) $jwt));

        LittleJWT::validate($mutated, $validatable);
    }

    /**
     * Tests that a custom mutator is mapped and resolved.
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

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('test');
            })->create(new TestBuildable(function (Builder $builder) {
                $builder->foo('abcd');
            }));

        $this->assertEquals('dcba', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests that a custom mutator is mapped and not resolved.
     *
     * @return void
     */
    public function test_mutate_custom_mapping_not_resolved()
    {
        LittleJWT::fake();

        $this->app->bind(TestMutator::class, function ($app) {
            return new TestMutator(
                fn ($value) => strrev($value),
                fn ($value) => strrev($value),
            );
        });

        LittleJWT::customMutator('test', TestMutator::class);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('tes');
            })->create(new TestBuildable(function (Builder $builder) {
                $builder->foo('abcd');
            }));

        $this->assertEquals('abcd', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests that a custom mutator key doesn't override a primitive key.
     *
     * @return void
     */
    public function test_invoke_mutates_custom_mapping_override()
    {
        LittleJWT::fake();

        $this->app->bind(TestMutator::class, function ($app) {
            return new TestMutator(
                fn ($value) => strrev($value),
                fn ($value) => strrev($value),
            );
        });

        LittleJWT::customMutator('int', TestMutator::class);

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('int');
            })->create(new TestBuildable(function (Builder $builder) {
                $builder->foo('1234');
            }));

        $this->assertNotEquals('4321', $jwt->getPayload()->get('foo'));
        $this->assertEquals('1234', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests that a custom mutator is added as a resolve method.
     *
     * @return void
     */
    public function test_invoke_mutates_custom_mapping_resolve()
    {
        LittleJWT::fake();

        MutatorResolver::macro('resolveTesting', function () {
            return new TestMutator(
                fn ($value) => strrev($value),
                fn ($value) => strrev($value),
            );
        });

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('testing');
            })->create(new TestBuildable(function (Builder $builder) {
                $builder->foo('1234');
            }));

        $this->assertNotEquals('1234', $jwt->getPayload()->get('foo'));
        $this->assertEquals('4321', $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests that a custom mutator is mapped and an exception is thrown when resolved.
     *
     * @return void
     */
    public function test_mutate_custom_mapping_not_resolved_exception()
    {
        LittleJWT::fake();

        $this->app->bind(TestMutator::class, function ($app) {
            return new TestMutator(
                fn ($value) => strrev($value),
                fn ($value) => strrev($value),
            );
        });

        $resolver = new MutatorResolver($this->app, [
            'test' => TestMutator::class,
        ]);

        $this->expectException(CantResolveMutator::class);

        $resolver->resolve('tes');
    }

    /**
     * Tests that a mutator with no return has the correct output value.
     *
     * @return void
     */
    public function test_invoke_mutates_custom_mutator_no_return()
    {
        LittleJWT::fake();

        $mutator = new TestMutator(
            function () {},
            function () {},
        );

        $serialized = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($mutator) {
                $mutators->foo($mutator);
            })->create(new TestBuildable(function (Builder $builder) {
                $builder->foo('abcd');
            }));

        $this->assertNull($serialized->getPayload()->get('foo'));

        $validated = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($mutator) {
                $mutators->foo($mutator);
            })->validate($serialized);

        $this->assertTrue($validated->passes());
        $this->assertNull($validated->unserialized()->getPayload()->get('foo'));
    }

    /**
     * Tests mutator is set from being set with Mutators in previous buildable call.
     *
     * @return void
     */
    public function test_mutator_isset_mutators_method()
    {
        $time = time();

        LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('date');
            })->mutate(function (Mutators $mutators) {
                $this->assertTrue(isset($mutators->foo));
                $this->assertFalse(isset($mutators->bar));

                $this->assertTrue($mutators->has('foo'));
                $this->assertFalse($mutators->has('bar'));
            })->create(new TestBuildable(function (Builder $builder) use ($time) {
                $builder->foo($time);
            }));
    }

    /**
     * Tests mutator is set from being set with Mutators magic property in previous buildable call.
     *
     * @return void
     */
    public function test_mutator_isset_mutators_property()
    {
        $time = time();

        LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo = 'date';
            })->mutate(function (Mutators $mutators) {
                $this->assertTrue(isset($mutators->foo));
                $this->assertFalse(isset($mutators->bar));

                $this->assertTrue($mutators->has('foo'));
                $this->assertFalse($mutators->has('bar'));
            })->create(new TestBuildable(function (Builder $builder) use ($time) {
                $builder->foo($time);
            }));
    }

    /**
     * Tests mutators for same claim is merged correctly.
     *
     * @return void
     */
    public function test_mutator_merged_correctly()
    {
        $time = time();

        $jwt = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) {
                $mutators->foo('datetime');
            })->mutate(function (Mutators $mutators) {
                $mutators->foo('date');
            })->mutate(function (Mutators $mutators) {
                $this->assertEquals('date', $mutators->foo);
            })->create(new TestBuildable(function (Builder $builder) use ($time) {
                $builder->foo($time);
            }));

        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests stack of mutatables
     *
     * @return void
     */
    public function test_mutator_stack_mutatables()
    {
        $reverse = new TestMutator(
            fn ($value) => strrev($value),
            fn ($value) => strrev($value),
        );

        $stack =
            (new StackMutatable)
                ->mutate(function (Mutators $mutators) {
                    $mutators->foo('datetime');
                })->mutate(function (Mutators $mutators) use ($reverse) {
                    $mutators
                        ->foo($reverse)
                        ->bar($reverse);
                });

        $serialized = LittleJWT::handler()
            ->mutate($stack)->create(new TestBuildable(function (Builder $builder) {
                $builder
                    ->foo('abcd')
                    ->bar('lmno');
            }));

        $this->assertEquals('dcba', $serialized->getPayload()->get('foo'));
        $this->assertEquals('onml', $serialized->getPayload()->get('bar'));

        $validated = LittleJWT::handler()->mutate($stack)->validate($serialized);

        $this->assertEquals('abcd', $validated->unserialized()->getPayload()->get('foo'));
        $this->assertEquals('lmno', $validated->unserialized()->getPayload()->get('bar'));
    }

    /**
     * Tests stack of mutators
     *
     * @return void
     */
    public function test_mutator_stack_mutators_reverse()
    {
        $stack =
            (new StackMutator)
                ->mutator(new TestMutator(
                    fn ($value) => strrev($value),
                    fn ($value) => strrev($value),
                ))->mutator(new TestMutator(
                    fn ($value) => strtoupper($value),
                    fn ($value) => strtolower($value),
                ));

        $handler = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($stack) {
                $mutators->foo($stack);
            });

        $serialized =
            $handler->create(new TestBuildable(function (Builder $builder) {
                $builder
                    ->foo('abcd');
            }));

        $this->assertEquals('DCBA', $serialized->getPayload()->get('foo'));

        $validated = $handler->validate($serialized);

        $this->assertEquals('abcd', $validated->unserialized()->getPayload()->get('foo'));
    }

    /**
     * Tests stack of mutators is ran in correct order.
     *
     * @return void
     */
    public function test_mutator_stack_mutators_order()
    {
        $stack =
            (new StackMutator)
                ->mutator(new TestMutator(
                    fn ($value) => $value.'b',
                    fn ($value) => 'b',
                ))->mutator(new TestMutator(
                    fn ($value) => $value.'c',
                    fn ($value) => 'c',
                ));

        $handler = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($stack) {
                $mutators->foo($stack);
            });

        $serialized =
            $handler->create(new TestBuildable(function (Builder $builder) {
                $builder
                    ->foo('a');
            }));

        $this->assertEquals('abc', $serialized->getPayload()->get('foo'));

        $validated = $handler->validate($serialized);

        $this->assertEquals('b', $validated->unserialized()->getPayload()->get('foo'));
    }

    /**
     * Tests stack of mutators is ran in correct order.
     *
     * @return void
     */
    public function test_mutator_stack_mutators_order_encrypt()
    {
        $stack =
            (new StackMutator)
                ->mutator(new DoubleMutator)
                ->mutator(new EncryptMutator);

        $handler = LittleJWT::handler()
            ->mutate(function (Mutators $mutators) use ($stack) {
                $mutators->foo($stack);
            });

        $serialized =
            $handler->create(new TestBuildable(function (Builder $builder) {
                $builder
                    ->foo(1234.1234);
            }));

        $this->assertNotEquals(1234.1234, $serialized->getPayload()->get('foo'));

        $validated = $handler->validate($serialized);

        $this->assertEquals(1234.1234, $validated->unserialized()->getPayload()->get('foo'));
    }

    /**
     * Tests default mutators are enabled.
     *
     * @return void
     */
    public function test_mutator_defaults_enabled()
    {
        $jwt = LittleJWT::create();

        $this->assertEquals('integer', gettype($jwt->getPayload()->get('iat')));
        $this->assertEquals('integer', gettype($jwt->getPayload()->get('nbf')));
        $this->assertEquals('integer', gettype($jwt->getPayload()->get('exp')));

        $this->assertGreaterThan(time(), $jwt->getPayload()->get('exp'));
    }

    /**
     * Tests default mutators are disabled.
     *
     * @return void
     */
    public function test_mutator_defaults_disabled()
    {
        $jwt = LittleJWT::applyDefaultMutators(false)->create();

        $this->assertNotEquals('integer', gettype($jwt->getPayload()->get('iat')));
        $this->assertNotEquals('integer', gettype($jwt->getPayload()->get('nbf')));
        $this->assertNotEquals('integer', gettype($jwt->getPayload()->get('exp')));
    }

    /**
     * Tests default mutators are disabled and additional mutators are used.
     *
     * @return void
     */
    public function test_mutator_defaults_disabled_additional()
    {
        $time = time();

        LittleJWT::applyDefaultMutators(false);

        $jwt = LittleJWT::mutate(function (Mutators $mutators) {
            $mutators->iat('date');
        })->create(function (Builder $builder) use ($time) {
            $builder->iat($time);
        });

        $this->assertEquals('string', gettype($jwt->getPayload()->get('iat')));
        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('iat'));
    }

    /**
     * Tests mutators are enabled.
     *
     * @return void
     */
    public function test_mutator_always_mutate_enabled()
    {
        $time = time();

        LittleJWT::alwaysMutate(true);

        $jwt = LittleJWT::mutate(function (Mutators $mutators) {
            $mutators->foo('date');
        })->create(function (Builder $builder) use ($time) {
            $builder->foo($time);
        });

        $this->assertEquals('string', gettype($jwt->getPayload()->get('foo')));
        $this->assertEquals(Carbon::parse($time)->format('Y-m-d'), $jwt->getPayload()->get('foo'));
    }

    /**
     * Tests mutators are disabled.
     *
     * @return void
     */
    public function test_mutator_always_mutate_disabled()
    {
        $time = time();

        LittleJWT::alwaysMutate(false);

        $this->expectException(\BadMethodCallException::class);

        LittleJWT::mutate(function (Mutators $mutators) {
            $mutators->foo('date');
        })->create(function (Builder $builder) use ($time) {
            $builder->foo($time);
        });
    }
}
