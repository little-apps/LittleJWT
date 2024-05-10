<?php

namespace LittleApps\LittleJWT\Testing;

interface TestValidatable
{
    /**
     * Performs the test validation on a JWT.
     *
     * @return void
     */
    public function validate(TestValidator $validator);
}
