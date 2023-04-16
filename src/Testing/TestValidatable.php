<?php

namespace LittleApps\LittleJWT\Testing;

interface TestValidatable
{
    /**
     * Performs the test validation on a JWT.
     *
     * @param TestValidator $validator
     * @return void
     */
    public function validate(TestValidator $validator);
}
