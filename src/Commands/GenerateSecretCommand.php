<?php

namespace LittleApps\LittleJWT\Commands;

use Illuminate\Console\Command;

use LittleApps\LittleJWT\Contracts\Keyable;

class GenerateSecretCommand extends Command
{
    public const ENV_KEY = 'LITTLEJWT_KEY_PHRASE';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'littlejwt:secret
        {--size=1024 : The size of the generated key in bits.}
        {--d|display : Displays the generated key instead of saving to .env file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a secret for Little JWT';

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
        $size = $this->option('size');

        $jwk = $this->laravel[Keyable::class]->generateRandomJwk($size);

        // The generated k is base64 encoded.
        $secret = $jwk->get('k');

        if ($this->option('display')) {
            $this->info('Generated secret key:');
            $this->info($secret);
        } else {
            if (! file_exists($this->envPath())) {
                $this->error(sprintf('Could not find environment file at "%s".', $this->envPath()));

                return 1;
            }

            $existingKey = env(static::ENV_KEY);

            if (is_null($existingKey)) {
                // Key does not exist in .env file yet.
                $append = sprintf('%s%s%s', PHP_EOL, $this->createLineForEnvFile($secret), PHP_EOL);

                file_put_contents($this->envPath(), $append, FILE_APPEND);
            } else {
                // Key already exists.
                $this->info('Secret already exists. Overwriting the secret will cause previous JWTs to be invalidated.');

                if (! $this->confirm('Overwrite existing JWT secret in .env file?')) {
                    return 1;
                }

                $contents = file_get_contents($this->envPath());

                $regex = sprintf('/(%s)=([^\s]+)/', static::ENV_KEY);
                $sub = $this->createLineForEnvFile($secret);

                $replaced = preg_replace($regex, $sub, $contents);

                file_put_contents($this->envPath(), $replaced);
            }

            $this->info('Little JWT secret was saved to .env file.');
        }

        return 0;
    }

    protected function createLineForEnvFile($value)
    {
        return sprintf('%s="%s"', static::ENV_KEY, $value);
    }

    protected function envPath()
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        } else {
            return sprintf('%s%s%s', $this->laravel->basePath(), DIRECTORY_SEPARATOR, '.env');
        }
    }
}
