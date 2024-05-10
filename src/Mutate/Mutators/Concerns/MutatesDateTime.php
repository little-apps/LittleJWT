<?php

namespace LittleApps\LittleJWT\Mutate\Mutators\Concerns;

use DateTimeInterface;
use Illuminate\Support\Carbon;

trait MutatesDateTime
{
    /**
     * Creates a Carbon instance from a value using an (optional) format.
     *
     * @param DateTimeInterface|string $value Existing DateTimeInterface or date/time formatted as a string.
     * @param string|null $format If a string, used as format in $value.
     * @return Carbon
     */
    protected function createCarbonInstance($value, $format = null)
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        return is_string($format) ? Carbon::createFromFormat($format, $value) : Carbon::parse($value);
    }
}
