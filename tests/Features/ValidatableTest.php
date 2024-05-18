<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Testing\TestValidatable;
use LittleApps\LittleJWT\Testing\TestValidator;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;
use LittleApps\LittleJWT\Validation\Validator;

class ValidatableTest extends TestCase
{
    use InteractsWithLittleJWT;
    use WithFaker;

    /**
     * Tests that a callback function is passed to validateJWT.
     *
     * @return void
     */
    public function test_callback_validatable()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        LittleJWT::validate($jwt, function (TestValidator $validator) {
            $validator
                ->assertPasses();
        });
    }

    /**
     * Tests that a custom validatable is passed to validateJWT.
     *
     * @return void
     */
    public function test_class_method_validatable()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        $validatable = new class() implements TestValidatable
        {
            public function validate(TestValidator $validator)
            {
                $validator->assertPasses();
            }
        };

        LittleJWT::validate($jwt, [$validatable, 'validate']);
    }

    /**
     * Tests that an invokable class is passed to validateJWT.
     *
     * @return void
     */
    public function test_invoke_validatable()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::create()->sign();

        $validatable = new class()
        {
            public function __invoke(TestValidator $validator)
            {
                $validator->assertPasses();
            }
        };

        LittleJWT::validate($jwt, $validatable);
    }

    /**
     * Tests that a validatable is included and JWT is valid.
     *
     * @return void
     */
    public function test_includes_validatable_valid()
    {
        LittleJWT::fake();

        $sub = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        })->sign();

        $validatable = new class($sub)
        {
            public function __construct(private readonly string $sub)
            {

            }

            public function __invoke(Validator $validator)
            {
                $validator->equals('sub', $this->sub);
            }
        };

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($validatable) {
            $validator->with($validatable)->assertPasses();
        });
    }

    /**
     * Tests that a validatable is included and JWT is invalid.
     *
     * @return void
     */
    public function test_includes_validatable_invalid()
    {
        LittleJWT::fake();

        $sub = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        })->sign();

        $validatable = new class($this->faker->uuid)
        {
            public function __construct(private readonly string $sub)
            {

            }

            public function __invoke(Validator $validator)
            {
                $validator->equals('sub', $this->sub);
            }
        };

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($validatable) {
            $validator->with($validatable)->assertFails();
        });
    }

    /**
     * Tests that a validatable is excluded and JWT is valid.
     *
     * @return void
     */
    public function test_excludes_validatable_invalid()
    {
        LittleJWT::fake();

        $sub = $this->faker->uuid;

        $jwt = LittleJWT::create(function (Builder $builder) use ($sub) {
            $builder->sub($sub);
        })->sign();

        $validatable = new class($this->faker->uuid)
        {
            public function __construct(private readonly string $sub)
            {

            }

            public function __invoke(Validator $validator)
            {
                $validator->equals('sub', $this->sub);
            }
        };

        LittleJWT::validate($jwt, function (TestValidator $validator) use ($validatable) {
            $validator
                ->with($validatable)
                ->without($validatable)
                ->assertPasses();
        });
    }
}
