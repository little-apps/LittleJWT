<?php

namespace LittleApps\LittleJWT\Commands;

use Illuminate\Console\Command;

class LittleJWTCommand extends Command
{
    public $signature = 'littlejwt';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
