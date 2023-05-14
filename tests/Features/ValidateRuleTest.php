<?php

namespace LittleApps\LittleJWT\Tests\Features;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

use LittleApps\LittleJWT\Build\Buildables\GuardBuildable;
use LittleApps\LittleJWT\Build\Buildables\StackBuildable;
use LittleApps\LittleJWT\Build\Builder;
use LittleApps\LittleJWT\Facades\LittleJWT;
use LittleApps\LittleJWT\Laravel\Rules\ValidToken;
use LittleApps\LittleJWT\Tests\Concerns\CreatesUser;
use LittleApps\LittleJWT\Tests\Concerns\InteractsWithLittleJWT;
use LittleApps\LittleJWT\Tests\TestCase;

class ValidateRuleTest extends TestCase
{
    use CreatesUser;
    use WithFaker;
    use InteractsWithLittleJWT;

    /**
     * Tests that the default token passes the ValidToken rule
     *
     * @return void
     */
    public function test_validtoken_passes()
    {
        $token = (string) LittleJWT::create()->sign();

        $validator = Validator::make(compact('token'), [
            'token' => [
                'required',
                new ValidToken(),
            ],
        ]);

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->messages()->any());
    }

    /**
     * Tests that the default token passes the implicit validtoken rule
     *
     * @return void
     */
    public function test_implicit_validtoken_passes()
    {
        $token = (string) LittleJWT::create()->sign();

        $validator = Validator::make(compact('token'), [
            'token' => [
                'required',
                'validtoken',
            ],
        ]);

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->messages()->any());
    }

    /**
     * Tests that an expired token fails the ValidToken rule
     *
     * @return void
     */
    public function test_validtoken_fails_expired()
    {
        $token = (string) LittleJWT::create(function (Builder $builder) {
            $builder->exp(Carbon::now()->subMonth());
        })->sign();

        $validator = Validator::make(compact('token'), [
            'token' => [
                'required',
                new ValidToken(),
            ],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->messages()->any());
    }

    /**
     * Tests that an expired token fails the implicit validtoken rule
     *
     * @return void
     */
    public function test_implicit_validtoken_fails_expired()
    {
        $token = (string) LittleJWT::create(function (Builder $builder) {
            $builder->exp(Carbon::now()->subMonth());
        })->sign();

        $validator = Validator::make(compact('token'), [
            'token' => [
                'required',
                'validtoken',
            ],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->messages()->any());
    }

    /**
     * Tests that a token with a valid sub fails the implicit validtoken rule
     *
     * @return void
     */
    public function test_implicit_validtoken_fails_valid_sub()
    {
        $buildable = new GuardBuildable($this->user);

        $token = (string) LittleJWT::create($buildable)->sign();

        $validator = Validator::make(compact('token'), [
            'token' => [
                'required',
                'validtoken:default,guard',
            ],
        ]);

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->messages()->any());
    }

    /**
     * Tests that a token with an invalid sub fails the implicit validtoken rule
     *
     * @return void
     */
    public function test_implicit_validtoken_fails_invalid_sub()
    {
        $stack = [
            new GuardBuildable($this->user),
            function (Builder $builder) {
                $builder->sub(
                    $this->faker
                        ->valid(fn ($num) => $num !== $this->user->getAuthIdentifier())
                        ->numberBetween(1, 999)
                );
            },
        ];

        $buildable = new StackBuildable($stack);

        $token = (string) LittleJWT::create($buildable)->sign();

        $validator = Validator::make(compact('token'), [
            'token' => [
                'required',
                'validtoken:default,guard',
            ],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->messages()->any());
    }
}
