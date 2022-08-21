<?php

namespace LittleApps\LittleJWT\Commands;

use Exception;
use Illuminate\Console\Command;

use LittleApps\LittleJWT\Blacklist\BlacklistManager;

class BlacklistPurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'littlejwt:purge {driver? : Blacklist driver to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges JWT blacklist entries in driver.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(BlacklistManager $blacklist)
    {
        try {
            $driver = $blacklist->driver($this->argument('driver'));

            $driver->purge();

            $this->info('Blacklist has been purged.');
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
