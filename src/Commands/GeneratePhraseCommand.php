<?php

namespace LittleApps\LittleJWT\Commands;

use Illuminate\Console\Command;
use LittleApps\LittleJWT\Commands\Concerns\GeneratesEnvVariables;
use LittleApps\LittleJWT\Factories\KeyBuilder;

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
        {--key=LITTLEJWT_KEY_PHRASE : The environment variable key.}
        {--size=1024 : The size of the generated key in bits.}
        {--d|display : Displays the generated key instead of saving to .env file.}
        {--y|yes : Answer yes to any prompts.}';

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
    public function handle()
    {
        $key = $this->option('key');
        $size = $this->option('size');
        $yes = $this->option('yes');

        if (! $this->isEnvKeyValid($key)) {
            $this->error('The environment variable key can only have letters, numbers, and underscores.');

            return 1;
        }

        if (!is_numeric($size) || $size <= 0 || $size % 8 !== 0) {
            $this->error('The key size must a positive number that is divisible by 8.');

            return 1;
        }

        $jwk = KeyBuilder::generateRandomJwk($size);

        // The generated k is base64 encoded.
        $secret = $jwk->get('k');

        if ($this->option('display')) {
            $this->info('Generated secret key:');
            $this->info($secret);
        } else {
            if (! file_exists($this->envPath()) || ! is_writable($this->envPath())) {
                $this->error(sprintf('The environment file "%s" does not exist or is not writable.', $this->envPath()));

                return 1;
            }

            if ($this->envKeyExists($key)) {
                $this->info('Secret already exists. Overwriting the secret will cause previous JWTs to be invalidated.');

                if (! $yes && ! $this->confirm('Overwrite existing JWT secret in .env file?')) {
                    return 1;
                }
            }

            if ($this->updateEnvFile($this->envPath(), [$key => $secret])) {
                $this->info('Little JWT secret was saved to .env file.');
            } else {
                $this->error(sprintf('An error occurred updating the "%s" file.', $this->envPath()));

                return 1;
            }
        }

        return 0;
    }
}
