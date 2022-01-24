<?php

namespace LittleApps\LittleJWT\Tests\Concerns;

use LittleApps\LittleJWT\Testing\Models\User;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

use Faker\Generator as Faker;

trait CreatesUser {
	protected $user;

    protected function setUpUser() {
        $this->user = $this->createUser();
    }

    /**
     * Creates a new user.
     *
     * @return User
     */
	protected function createUser() {
		return User::factory()->create();
	}

	/**
	 * Changes the users current password and returns it in plain-text.
	 *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user User to get current password for. If null, get's current users password. (Default is null)
	 * @return string
	 */
	protected function getCurrentPassword(Authenticatable $user = null) {
		// Check if we can use the Faker from WithFaker or create our own instance.
		$faker = isset($this->faker) ? $this->faker : app(Faker::class);

		return tap($faker->unique()->password, function($password) use($user) {
            (!is_null($user) ? $user : $this->user)
				->setAttribute('password', Hash::make($password))
				->save();
		});
	}
}
