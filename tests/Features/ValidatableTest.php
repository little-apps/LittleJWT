<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;

use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Testing\TestValidatable;
use LittleApps\LittleJWT\Testing\TestValidator;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class ValidatableTest extends TestCase
{
    use WithFaker;
    use InteractsWithLittleJWT;

    /**
     * Tests that a callback function is passed to validateJWT.
     *
     * @return void
     */
    public function test_callback_validatable()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::createJWT();

        LittleJWT::validateJWT($jwt, function (TestValidator $validator) {
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

        $jwt = LittleJWT::createJWT();

        $validatable = new class () implements TestValidatable {
            public function validate(TestValidator $validator)
            {
                $validator->assertPasses();
            }
        };

        LittleJWT::validateJWT($jwt, [$validatable, 'validate']);
    }

    /**
     * Tests that an invokable class is passed to validateJWT.
     *
     * @return void
     */
    public function test_invoke_validatable()
    {
        LittleJWT::fake();

        $jwt = LittleJWT::createJWT();

        $validatable = new class () {
            public function __invoke(TestValidator $validator)
            {
                $validator->assertPasses();
            }
        };

        LittleJWT::validateJWT($jwt, $validatable);
    }
}
