<?php

namespace LittleApps\LittleJWT\Commands;

use Illuminate\Console\Command;

use LittleApps\LittleJWT\Commands\Concerns\GeneratesEnvVariables;
use LittleApps\LittleJWT\Contracts\Keyable;

class GeneratePhraseCommand extends Command
{
    use GeneratesEnvVariables;

    public const ENV_KEY = 'LITTLEJWT_KEY_PHRASE';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'littlejwt:phrase
        {--size=1024 : The size of the generated key in bits.}
        {--d|display : Displays the generated key instead of saving to .env file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a secret phrase for Little JWT';

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
    public function handle(Keyable $keyable)
    {
        $size = $this->option('size');

        $jwk = $keyable->generateRandomJwk($size);

        // The generated k is base64 encoded.
        $secret = $jwk->get('k');

        if ($this->option('display')) {
            $this->info('Generated secret key:');
            $this->info($secret);
        } else {
            if ($this->envKeyExists(static::ENV_KEY)) {
                $this->info('Secret already exists. Overwriting the secret will cause previous JWTs to be invalidated.');

                if (! $this->confirm('Overwrite existing JWT secret in .env file?')) {
                    return 1;
                }
            }

            if ($this->updateEnvFile($this->envPath(), [static::ENV_KEY => $secret])) {
                $this->info('Little JWT secret was saved to .env file.');
            } else {
                $this->error(sprintf('An error occurred updating the "%s" file.', $this->envPath()));

                return 1;
            }

        }

        return 0;
    }


}
