<?php

namespace LittleApps\LittleJWT\Tests\Concerns;

use Exception;
use Illuminate\Support\Carbon;

trait InteractsWithTimeBackwardsCompatible
{
    /**
     * Begin travelling to another time.
     *
     * @param  int  $value
     * @return \Illuminate\Foundation\Testing\Wormhole
     */
    public function travel($value)
    {
        throw new Exception('Use the travelTo method to set the current date/time.');
    }

    /**
     * Travel to another time.
     *
     * @param  \DateTimeInterface  $date
     * @param  callable|null  $callback
     * @return mixed
     */
    public function travelTo($date, $callback = null)
    {
        Carbon::setTestNow($date);

        if ($callback) {
            return tap($callback(), function () {
                Carbon::setTestNow();
            });
        }
    }

    /**
     * Travel back to the current time.
     *
     * @return \DateTimeInterface
     */
    public function travelBack()
    {
        Carbon::setTestNow();

        return Carbon::now();
    }
}
